=== Plugin Name ===
Contributors: Rocky1983
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=paypal%40amegrant%2ehu&lc=HU&item_name=Diamond%20Multisite%20WordPress%20Widget&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted
Tags: recent post, network, multisite, widget, recent comments, MU, WPMU, sidebar, broadcast, copy post, share post, network post
Requires at least: 3.0.0
Tested up to: 3.0.1
Stable tag: 1.4

<b>Recent posts and comments from the whole network<br />
Broadcast posts
</b>

== Description ==

<h3>Recent posts and comments from the whole network</h3>
<br />
<h4>Features:</h4><br />
- You can choose the entries count<br />
- You can exclude your main blog or any sub-blogs<br />
- Yuu can format the entries easily with short codes and html tags <br />
- You can set custom datetime format<br />
- You can use it on sub-blogs<br />
- Avatar support<br />
<br />
- Post/Page shortcodes support:<br />
- Usage:<br />
    Recent Posts
	
	[diamond-post  /]
	<br />
	This shows tha last 3 posts horizontally in a table on a page or a post.
	
	[diamond-post format="{avatar}- {title} - {author} - {date}" before_content="&lt;table&gt;&lt;tr&gt;" after_content="&lt;/tr&gt;&lt;/table&gt;" before_item="&lt;td&gt;" after_item="&lt;/td&gt;" count=3  /]
	<br />
	
	
	- Recent Comments
	  [diamond-comment  /]
	<br />
	This shows tha last 3 comments horizontally in a table.
	
	[diamond-post format="{avatar}- {title} - {author} - {date}" before_content="&lt;table&gt;&lt;tr&gt;" after_content="&lt;/tr&gt;&lt;/table&gt;" before_item="&lt;td&gt;" after_item="&lt;/td&gt;" count=3  /]
	<br />
	
	<h5>Attributes:</h5>
	<ul>
		<li>format: format string. You can use the widget's shortcodes!</li>
		<li>before_content: Before the entry-list (Default: &lt;ul&gt;)</li>
		<li>after_content: After the entry-list (Default: &lt;/ul&gt;)</li>
		<li>before_item: Before the entry-list item (Default: &lt;li&gt;)</li>
		<li>after_item: After the entry-list item (Default: &lt;/li&gt;)</li>
		<li>exclude: Blogs' id you want to exclude (separate with ',')</li>
		<li>count: Entry count limit</li>
		<li>avatar_size: Author's avatar's size (px) </li>
		<li>default_avatar: Custom default avatar's URL</li>
		<li>date_format: Datetime format string</li>
	</ul>
<br />
Broadcast Post On The Network<br />
- In the publish box, you can copy your post to the network's sub-blogs<br />
<br />
if you have any question write me an e-mail to daniel.bozo@amegrant.hu

== Changelog ==

= 1.4 =

- Shortcodes suppor for Pages and Posts	
- Refactor the render mechanism.

= 1.3.1 =

- {post-title}, {post-title_txt} schortcodes added to diamond recent comments widget

= 1.3 =

-  Broadcast post

= 1.2.3 =

- Avatar size bugfix

= 1.2.2 =

- Custom datetime format

= 1.2.1 =

- Avatar size disappears after save bugfix
- 'Read more' link shorcode documentation added

= 1.2 =

- Avatar support
- 'Read more' link added
- Excerpt support

= 1.1 =

- Now you can use it on sub-blogs












