<?php
/*
* SiteSense
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@sitesense.org so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade SiteSense to newer
* versions in the future. If you wish to customize SiteSense for your
* needs please refer to http://www.sitesense.org for more information.
*
* @author     Full Ambit Media, LLC <pr@fullambit.com>
* @copyright  Copyright (c) 2011 Full Ambit Media, LLC (http://www.fullambit.com)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
common_include('libraries/forms.php');

function blogs_getUniqueSettings($data) {
	$data->output['pageTitle']='Blog';
}
function blogs_buildContent($data, $db) {
	common_include('modules/blogs/blogs.common.php');
	$data->output['summarize']=false;
	$data->output['notFound']=false;
	// Now Get Users
	$statement=$db->prepare('getAllUsers', 'users');
	$statement->execute();
	$data->output['usersList']=$statement->fetchAll();
	// Get the ID Of The Blog Based On The ShortName
	if (!is_numeric($data->action[1])) {
		$statement=$db->prepare('getBlogByName', 'blogs');
		$statement->execute(array(
				':shortName' => $data->action[1]
			));
		$parentBlog=$statement->fetch();
		$data->output['blogInfo']=$parentBlog;
		$data->action[1]=$parentBlog['id'];
	} else {
		$statement=$db->prepare('getBlogById', 'blogs');
		$statement->bindValue(':blogId', $data->action[1]);
		$statement->execute();
		$data->output['blogInfo']=$statement->fetch();
	}
	// Blog Not Found
	if ($data->output['blogInfo']===false) {
		$data->output['notFound']=true;
		return;
	}
	$data->output['pageShortName']=$data->output['blogInfo']['shortName'];
	$data->output['pageTitle']=ucwords($data->output['blogInfo']['title']);
	// Get All Blog Categories
	$statement=$db->prepare('getAllCategoriesByBlogId', 'blogs');
	$statement->execute(array(
			':blogId' => $data->output['blogInfo']['id']
		));
	$data->output['blogCategoryList']=$statement->fetchAll();
	// Build a localRoot so that links can account for top-level blogs
	$result=$db->query('getTopLevelBlogs', 'blogs');
	$data->topLevelBlogs=array();
	while ($row=$result->fetch()) {
		$data->topLevelBlogs[]=$row['name'];
	}
	$data->localRoot=$data->linkRoot.(in_array($data->output['blogInfo']['name'], $data->topLevelBlogs)
		? $data->output['blogInfo']['name']
		: 'blogs/'.$data->output['blogInfo']['shortName']);
	$data->output['rssLink']=isset($data->output['blogInfo']['rssOverride']{1}) ? $data->output['blogInfo']['rssOverride'] : $data->localRoot.'/rss';
	// If RSS Feed Skip All This
	if ($data->action[2]==='rss') {
		// Content Type
		$data->httpHeaders[]='Content-Type: application/xml';
		// Get Blog Info
		$statement=$db->prepare('getBlogById', 'blogs');
		$statement->execute(array(
				':blogId' => $data->action[1]
		));
		$data->output['blogItem']=$statement->fetch();
		// Get All Posts In Blog
		$statement=$db->prepare('getBlogPostsByParentBlog', 'blogs');
		$statement->execute(array(
				':blogId' => $data->action[1]
			));
		$data->output['postsList']=$statement->fetchAll();
		// Get All Categories in Blog
		$statement=$db->prepare('getAllCategoriesByBlogId', 'blogs');
		$statement->execute(array(':blogId' => $data->action[1]));
		$catList=$statement->fetchAll();
		foreach ($catList as $catItem) {
			$data->output['rssCategoryList'][$catItem['id']]=$catItem;
		}
		// Get Name Of Blog Owner
		if ($data->output['blogItem']['owner'] != '0') {
			$statement=$db->prepare('getById', 'users');
			$statement->execute(array(
					':id' => $blogItem['owner']
				));
			$data->output['blogOwnerItem']=$statement->fetch();
		}
		// Get Total Authors
		$statement=$db->prepare('getUniqueAuthorCountByBlog', 'blogs');
		$statement->execute(array(
				':blogId' => $data->action[1]
			));
		$data->output['blogItem']['authorCount']=$statement->fetch();
		if ($data->output['blogItem']['authorCount'] > 1) {
			// Get A List Of All Users And Resort Using The ID As The Key
			$statement=$db->prepare("getAllUsers", 'users');
			$statement->execute();
			$result=$statement->fetchAll();
			foreach ($result as $index => $userItem) {
				$data->output['blogItem']['userList'][$userItem['id']]=$userItem;
			}
		}
	} else {
		// If No Page Set, Then Start At 0
		if (($data->action[2]==='tags'||$data->action[2]==='categories')&&$data->action[4]===false) {
			$data->action[4]=0;
		} elseif ($data->action[2]===false) {
			$data->action[2]=0;
		}
		// grab posts for a blog/tags/categories and build the listing
		if (is_numeric($data->action[2])||$data->action[2]==='tags'||$data->action[2]==='categories') {
			if ($data->action[2]==='categories') {
				// Get The ID Of The Category Based Off The ShortName
				$statement=$db->prepare('getCategoryIdByShortName', 'blogs');
				$statement->execute(array(
						':shortName' => $data->action[3]
				));
				$data->output['categoryItem']=$statement->fetch();
			}
			$data->output['summarize']=true;
			blogs_common_buildContent($data, $db);
			foreach ($data->output['newsList'] as &$item) {
				$statement=$db->prepare('countCommentsByPost', 'blogs');
				$statement->execute(array('post' => $item['id']));
				$item['commentCount']=$statement->fetchColumn();
			}
		} else {
			// Viewing A Specific Post Within A Blog
			$statement=$db->prepare('getBlogPostsByIDandName', 'blogs');
			$statement->execute(array(
					':blogId' => $data->action[1],
					':shortName' => $data->action[2]
				));
			$data->output['newsList']=$statement->fetchAll();
			$data->output['pageTitle']=$data->output['newsList'][0]['title'].' - Blog';
			// If No Posts, Return An Error
			if (empty($data->output['newsList'])) {
				$data->output['notFound']=true;
				return;
			}
			if (($data->output['newsList'][0]['allowComments']=='1')) {
				$data->output['commentForm']=new formHandler('comment', $data);
				$data->output['commentForm']->fields['post']['value']=$data->output['newsList'][0]['id'];

				if (isset($_POST['fromForm']) && $_POST['fromForm']==$data->output['commentForm']->fromForm) {
					$data->output['commentForm']->populateFromPostData();
					if ($data->output['commentForm']->validateFromPost()) {
						$statement=$db->prepare('makeComment', 'blogs');
						// BBCode Parsing
						if ($data->settings['useBBCode']=='1') {
							if (!isset($data->plugins['bbcode'])) {
								common_loadPlugin($data, 'bbcode');
							}
							$data->output['commentForm']->sendArray[':parsedContent']=$data->plugins['bbcode']->parse($data->output['commentForm']->sendArray[':rawContent']);
						} else {
							$data->output['commentForm']->sendArray[':parsedContent']=htmlspecialchars($data->output['commentForm']->sendArray[':rawContent']);
						}
						// Remove subscriptions; not stored in our database
						unset($data->output['commentForm']->sendArray[':subscription']);
						$statement->execute($data->output['commentForm']->sendArray);
						unset($data->output['commentForm']);
						$data->output['commentSuccess']=true;
					}
				}
			}
		}

		// Call The Theme Functions And Generate The Posts
		foreach ($data->output['newsList'] as &$item) {
			$statement=$db->prepare('getApprovedCommentsByPost', 'blogs');
			$statement->execute(array('post' => $item['id']));
			$item['comments']=$statement->fetchAll();
			$item['commentCount']=count($item['comments']);
			// Get A Count Of All Comments Awaiting Approval
			$statement=$db->prepare('getCommentsAwaitingApproval', 'blogs');
			$statement->execute(array('post' => $item['id']));
			$result=$statement->fetch();
			$item['commentsWaiting']=intval($result[0]);
		}
	}
}
function blogs_content($data) {
	if ($data->action[2]==='rss') {
		theme_blogRSSFeed($data);
		die;
	} else {
		if(isset($data->output['blogInfo']['numberOfPosts']) && $data->output['blogInfo']['numberOfPosts'] > $data->output['blogInfo']['numberPerPage']){
			$pagination = true;
		}else{
			$pagination = false;
		}
		blogs_common_pageContent($data, false, $pagination, $data->output['summarize']);
	}
}