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
/*
	!table! = $tableName
	!prefix! = dynamicPDO::tablePrefix
*/
function admin_blogs_addQueries() {
	return array(
        'getAllBlogs' => '
			SELECT * FROM !prefix!blogs!lang!
		',
        'countBlogs' => '
			SELECT count(id) AS count
			FROM !prefix!blogs!lang!
		',
        'countBlogPosts' => '
			SELECT count(id) FROM !prefix!blog_posts!lang!
			WHERE blogId = :blogId
		',
        'getBlogById' => '
			SELECT * FROM !prefix!blogs!lang!
			WHERE id = :id
		',
        'getBlogByIdAndOwner' => '
			SELECT * FROM !prefix!blogs!lang!
			WHERE id = :id AND owner = :owner
		',
        'getBlogByPost' => '
			SELECT * FROM !prefix!blogs!lang! WHERE id IN (SELECT blogId FROM !prefix!blog_posts!lang! WHERE id = :postId)
		',
        'deleteBlogById' => '
			DELETE FROM !prefix!blogs!lang!
			WHERE id = :id
		',
        'deleteBlogPostByBlogId' => '
			DELETE FROM !prefix!blog_posts!lang!
			WHERE blogId = :id
		',
        'getBlogPostsById' => '
			SELECT *,
			UNIX_TIMESTAMP(CONCAT(modifiedTime,"+00:00")) AS modifiedTime,
			UNIX_TIMESTAMP(CONCAT(postTime,"+00:00")) AS postTime
			FROM !prefix!blog_posts!lang!
			WHERE id = :id
		',
        'deleteBlogPostById' => '
			DELETE FROM !prefix!blog_posts!lang!
			WHERE id = :id
		',
        'getBlogIdByName' => '
			SELECT id FROM !prefix!blogs!lang!
			WHERE shortName = :shortName
		',
        'getBlogPostIdByName' => '
			SELECT id FROM !prefix!blog_posts!lang!
			WHERE shortName = :shortName
		',
        'getUsersWithBlogAccess' => '
		',
        'updateBlogById' => '
			UPDATE !prefix!blogs!lang!
			SET
				shortName            =   :shortName,
				name                 =   :name,
				title				 =	 :title,
				owner                =   :owner,
				allowComments		 =	 :allowComments,
				numberPerPage        =   :numberPerPage,
				description          =   :description,
				commentsRequireLogin =   :commentsRequireLogin,
				topLevel             =   :topLevel,
				managingEditor = :managingEditor,
				webMaster = :webMaster,
				rssOverride = :rssOverride
			WHERE id = :id
		',
        'insertBlog' => '
			INSERT INTO !prefix!blogs!lang!
			(name,title,managingEditor,shortName,owner,allowComments,numberPerPage,description,commentsRequireLogin, topLevel, webMaster,rssOverride) VALUES (:name, :title, :managingEditor, :shortName, :owner, :allowComments, :numberPerPage, :description, :commentsRequireLogin, :topLevel, :webMaster, :rssOverride)
		',
        'updateShortNameById' => '
			UPDATE !prefix!blogs!lang!
			SET shortName = :shortName
			WHERE id = :id
		',
        'updateBlogPostsById' => '
			UPDATE !prefix!blog_posts!lang!
			SET
				categoryId = :categoryId,
				title        = :title,
				name		 = :name,
				shortName    = :shortName,
				rawSummary      = :rawSummary,
				parsedSummary	= :parsedSummary,
				rawContent      = :rawContent,
				parsedContent	= :parsedContent,
				live         = :live,
				tags         = :tags,
				allowComments = :allowComments
			WHERE id = :id
		',
        'insertBlogPost' => '
			INSERT INTO !prefix!blog_posts!lang!(
				blogId,
				categoryId,
				title,
				name,
				shortName,
				user,
				postTime,
				rawSummary,
				parsedSummary,
				rawContent,
				parsedContent,
				live,
				tags,
				allowComments
			) VALUES (
				:blogId,
				:categoryId,
				:title,
				:name,
				:shortName,
				:user,
				CURRENT_TIMESTAMP,
				:rawSummary,
				:parsedSummary,
				:rawContent,
				:parsedContent,
				:live,
				:tags,
				:allowComments
			)',
        'updatePostShortNameById' => '
			UPDATE !prefix!blog_posts!lang!
			SET shortName = :shortName
			WHERE id = :id
		',
        'getBlogsByOwner' => '
			SELECT * FROM !prefix!blogs!lang!
			ORDER BY owner
			LIMIT :blogStart, :blogLimit
		',
        'getBlogsByUser' => '
			SELECT * FROM !prefix!blogs!lang!
			WHERE owner = :owner
			ORDER BY owner
			LIMIT :blogStart, :blogLimit
		',
        'countBlogPostsByBlogId' => '
			SELECT COUNT(id) AS count
			FROM !prefix!blog_posts!lang!
			WHERE blogID = :id
		',
        'getBlogPostsByBlogIdLimited' => '
			SELECT *,
			UNIX_TIMESTAMP(CONCAT(modifiedTime,"+00:00")) AS modifiedTime,
			UNIX_TIMESTAMP(CONCAT(postTime,"+00:00")) AS postTime
			FROM !prefix!blog_posts!lang!
			WHERE blogId = :blogId
			ORDER BY postTime DESC
			LIMIT :blogStart, :blogLimit
		',
        'getAllCategories' => '
			SELECT *
				FROM !prefix!blog_categories!lang!
				ORDER BY name ASC
		',
        'getAllCategoriesByBlog' => '
			SELECT *
				FROM !prefix!blog_categories!lang!
				WHERE blogId = :blogId
				ORDER BY name ASC
		',
        'getCategoryById' => '
			SELECT * FROM !prefix!blog_categories!lang! WHERE id = :id
		',
        'editCategory' => '
			UPDATE !prefix!blog_categories!lang! SET name = :name, shortName = :shortName WHERE id = :id LIMIT 1
		',
        'deleteCategory' => '
			DELETE FROM !prefix!blog_categories!lang! WHERE id = :id
		',
        'updatePostsWithinCategory' => '
			UPDATE !prefix!blog_posts!lang! SET categoryId = 0 WHERE categoryId = :categoryId
		',
        'addCategory' => '
			INSERT INTO !prefix!blog_categories!lang! (blogId,name,shortName) VALUES (:blogId,:name,:shortName)
		',
        'getExistingShortNames' => '
			SELECT shortName FROM !prefix!blog_posts!lang!
		',
        'getExistingBlogShortNames' => '
			SELECT shortName FROM !prefix!blogs!lang!
		',
        'getCommentById' => '
			SELECT *,UNIX_TIMESTAMP(CONCAT(time,"+00:00")) AS time FROM !prefix!blog_comments
			WHERE id = :id
		',
        'getApprovedCommentsByPost' => '
			SELECT *,UNIX_TIMESTAMP(CONCAT(time,"+00:00")) AS time FROM !prefix!blog_comments
			WHERE post = :post AND approved = 1
			ORDER BY `time` ASC
		',
        'getDisapprovedCommentsByPost' => '
			SELECT *,UNIX_TIMESTAMP(CONCAT(time,"+00:00")) AS time FROM !prefix!blog_comments
			WHERE post = :post AND approved = -1
			ORDER BY `time` ASC
		',
        'editCommentById' => '
			UPDATE !prefix!blog_comments SET authorFirstName = :authorFirstName, authorLastName = :authorLastName, rawContent = :rawContent, parsedContent = :parsedContent, email = :email WHERE id = :id
		',
        'deleteCommentById' => '
			DELETE FROM !prefix!blog_comments WHERE id = :id
		',
        'countCommentsByPost' => '
			SELECT count(id) AS count
			FROM !prefix!blog_comments
			WHERE post = :post
		',
        'makeComment' => '
			INSERT INTO !prefix!blog_comments
			(post, authorFirstName, authorLastName, content,email,loggedIP)
			VALUES
			(:post, :authorFirstName, :authorLastName, :content,:email,:loggedIP)
		',
        'getCommentsAwaitingApproval' =>'
			SELECT *,UNIX_TIMESTAMP(CONCAT(time,"+00:00")) AS time FROM !prefix!blog_comments
			WHERE post = :post AND approved = 0
			ORDER BY `time` ASC
		',
        'approveComment' => '
			UPDATE !prefix!blog_comments SET approved = 1 WHERE id = :id LIMIT 1
		',
        'disapproveComment' => '
			UPDATE !prefix!blog_comments SET approved = -1 WHERE id = :id LIMIT 1
		'
    );
}
?>