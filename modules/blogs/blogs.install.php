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
function blogs_settings() {
	return array(
		'name'      => 'blogs',
		'shortName' => 'blogs',
		'version'   => '1.0.2'
	);
}
function blogs_install($db, $drop=false, $firstRun=false, $lang='en_us') {
	$structures=array(
		'blogs' => array(
			'id'                   => SQR_IDKey,
			'managingEditor'       => SQR_email,
			'webMaster'            => SQR_email,
			'name'                 => SQR_name,
			'shortName'            => SQR_shortName,
			'title'                => SQR_title,
			'owner'                => SQR_ID,
			'numberPerPage'        => 'TINYINT UNSIGNED NOT NULL',
			'description'          => 'TEXT NOT NULL',
			'allowComments'        => SQR_boolean.' DEFAULT \'1\'',
			'commentsRequireLogin' => SQR_boolean,
			'topLevel'             => SQR_boolean,
			'rssOverride'          => SQR_URL,
			'KEY `name` (`name`,`owner`)'
		),
		'blog_categories' => array(
			'id'                   => SQR_IDKey,
			'blogId'               => SQR_ID,
			'name'                 => SQR_title,
			'shortName'            => SQR_title
		),
		'blog_comments' => array(
			'id'                   => SQR_IDKey,
			'post'                 => SQR_ID,
			'time'                 => SQR_added,
			'authorFirstName'      => SQR_firstName,
			'authorLastName'       => SQR_lastName,
			'rawContent'           => 'TEXT NOT NULL',
			'parsedContent'        => 'TEXT NOT NULL',
			'email'                => SQR_email,
			'loggedIP'             => SQR_IP,
			'approved'             => SQR_boolean.' DEFAULT \'0\''
		),
		'blog_posts' => array(
			'id'                   => SQR_IDKey,
			'blogId'               => SQR_ID,
			'categoryId'           => SQR_ID.' DEFAULT \'0\'',
			'title'                => SQR_title,
			'name'                 => SQR_title,
			'shortName'            => SQR_title,
			'user'                 => SQR_ID,
			'postTime'             => SQR_time,
			'modifiedTime'         => SQR_lastModified,
			'live'                 => SQR_boolean,
			'rawSummary'           => 'TEXT NOT NULL',
			'parsedSummary'        => 'TEXT NOT NULL',
			'rawContent'           => 'TEXT NOT NULL',
			'parsedContent'        => 'MEDIUMTEXT NOT NULL',
			'description'          => 'TEXT NOT NULL',
			'allowComments'        => SQR_boolean,
			'repliesWaiting'       => SQR_boolean,
			'tags'                 => 'TINYTEXT NOT NULL',
			'KEY `blogId` (`blogId`)'
		)
	);
	if ($drop)
		blogs_uninstall($db);

	$db->createTable('blogs', $structures['blogs'], $lang);
	$db->createTable('blog_posts', $structures['blog_posts'], $lang);
	$db->createTable('blog_comments', $structures['blog_comments']);
	$db->createTable('blog_categories', $structures['blog_categories'], $lang);

	if ($firstRun) {
		// Add Blog Category Sidebar
		$statement=$db->query('getHighestSortOrder', 'admin', array('!table!'=>'sidebars'.'_'.$lang, '!column1!' => 'sortOrder'));
		if ($result=$statement->fetch()) {
			$sortOrder=1;
		} else {
			$sortOrder=$result['sortOrder']+1;
		}

		$statement=$db->prepare('insertSidebar', 'admin_sidebars', array('!lang!'=>$lang));
		$statement->execute(array(
				':name'           => 'Blog Categories',
				':shortName'      => 'blog-categories',
				':title'          => 'Blog Categories',
				':side'           => 'left',
				':titleURL'       => 'blog-categories',
				':sortOrder'      => $sortOrder,
				':rawContent'     => '
	    <div class="buttonWrapper">
	        <a href="|rssLink|" class="greenButton">
	            <span>
	                <b></b>Subscribe
	                <i><!-- hover state precache --></i>
	            </span>
	        </a>
	    </div>
	    |block:blogs_categories|',
				':parsedContent'  => '
	    <div class="buttonWrapper">
	        <a href="|rssLink|" class="greenButton">
	            <span>
	                <b></b>Subscribe
	                <i><!-- hover state precache --></i>
	            </span>
	        </a>
	    </div>
	    |block:blogs_categories|'
			));

		// Set up default permission groups
		$defaultPermissionGroups=array(
			'Writer' => array(
				'blogs_access',
				'blogs_accessOthers',

				'blogs_blogAdd',
				'blogs_blogEdit',
				'blogs_blogDelete',
				'blogs_blogList',

				'blogs_categoryAdd',
				'blogs_categoryEdit',
				'blogs_categoryDelete',
				'blogs_categoryView',

				'blogs_commentAdd',
				'blogs_commentEdit',
				'blogs_commentDelete',
				'blogs_commentApprove',
				'blogs_commentDisapprove',
				'blogs_commentList',

				'blogs_postAdd',
				'blogs_postEdit',
				'blogs_postDelete',
				'blogs_postList'
			),
			'Moderator' => array(
				'blogs_access',
				'blogs_accessOthers',

				'blogs_blogList',

				'blogs_categoryView',

				'blogs_commentAdd',
				'blogs_commentEdit',
				'blogs_commentDelete',
				'blogs_commentApprove',
				'blogs_commentDisapprove',
				'blogs_commentList',

				'blogs_postList'

			),
			'Blogger' => array(
				'blogs_access',

				'blogs_blogAdd',
				'blogs_blogEdit',
				'blogs_blogDelete',
				'blogs_blogList',

				'blogs_categoryAdd',
				'blogs_categoryEdit',
				'blogs_categoryDelete',
				'blogs_categoryView',

				'blogs_commentAdd',
				'blogs_commentEdit',
				'blogs_commentDelete',
				'blogs_commentApprove',
				'blogs_commentDisapprove',
				'blogs_commentList',

				'blogs_postAdd',
				'blogs_postEdit',
				'blogs_postDelete',
				'blogs_postList'
			),
		);
		foreach ($defaultPermissionGroups as $groupName => $permissions) {
			foreach ($permissions as $permissionName) {
				$statement=$db->prepare('addPermissionByGroupName');
				$statement->execute(
					array(
						':groupName' => $groupName,
						':permissionName' => $permissionName,
                    	':value' => '1'
					)
				);
			}
		}
		// ---
		if ($db->countRows('blogs'.'_'.$lang)==0) {
			try {
				echo '
					<h3>Attempting:</h3>';
				$db->exec('makeNewsBlog', 'installer', array('!lang!'=>$lang));
				echo '
					<div>
						Home Page News Blog Generated!
					</div><br />
				';
			} catch(PDOException $e) {
				$installErrors++;
				echo '
					<h2>Failed to create Home Page News Blog!</h2>
					<pre>'.$e->getMessage().'</pre><br />
				';
			}
		} else echo '<p class="exists">"blogs database" already contains records</p>';

		$count=$db->countRows('blog_posts'.'_'.$lang);
		if ($count==0) {
			try {
				echo '
					<h3>Attempting to add Welcome Post</h3>';
				$statement=$db->query('makeWelcomePost', 'installer', array('!lang!'=>$lang));
				echo '
					<div>
						Home Page Welcome Post Generated!<br />
					</div><br />';
			} catch(PDOException $e) {
				$db->installErrors++;
				echo '
					<p class="error">Failed to create Home Page Welcome Post!</p>
					<pre>'.$e->getMessage().'</pre><br />
				';
			}
		} else echo '<p class="exists">"blogs database" already contains records</p>';
	}
}

function blogs_uninstall($db, $lang="en_us") {
	// Get All Installed Languages
	$db->dropTable('blogs', $lang);
	$db->dropTable('blog_posts', $lang);
	$db->dropTable('blog_comments');
	$db->dropTable('blog_categories', $lang);
}
?>