<?php 
   include 'top.php';
   ?>

<h2>Tools</h2>

Here are some tools to help better use this service.


<p>
	To send content, when you're on a page or see content you'd like to
	share, control-click on that item, and select <em>Urlumize</em>,
	like so:
	<blockquote>
		<img src="img/ss-0.14.png" />
	</blockquote>
	The link that is created depends on the link that it sent:
	<ul>
		<li>
			If you just pop up the menu on a page, that page's link will be sent.
		</li>
		<li>
			If you select some content or text on the page, the location of
			that object will be embedded in the sent url so users' browsers
			with this add on installed will focus on that part of the page
			when this link is visited.
		</li>
		<li>
			If you select an image, that image's location will be sent.
		</li>
		<li>
			If you a link, that link will be sent.
		</li>
	</ul>
As of verison 0.14, after this link is created you can send it
<ul>
<li>By opening a new urlu.ms page</li>
<li>Posting to Twitter</li>
<li>Sending via email</li>
<li>Sending via AOL instant messenger</li>
<li>Sending via Yahoo! messenger</li>
</ul>
</p>

<a name="bookmarklet"></a>
<h3>Bookmarklet</h3>
<p>
	Instead of using the page directly, you can use this bookmarklet
	when you're on a page whose link is badder than it could be
</p>

<blockquote>
	<a href="javascript:document.location='<?php echo fullUrl(); ?>?u='+escape(document.location)">urlumize</a>
</blockquote>

<a name="clicks"></a>
<h3>Clicks</h3>

<p>
The <a href="/clicks.php">clicks</a> page will give you stats on the
links you create.  Another way of seeing these is to add a <em>+</em>
of a link.  For example, to see the stats
of <a class="url" href="http://urlu.ms/YrpULv">http://urlu.ms/YrpULv</a> use this
link:
</p>

<blockquote>
  <a class="url" href="http://urlu.ms/YrpULv+">http://urlu.ms/YrpULv+</a>
</blockquote>

<p>
	As more tools come about chances are they'll end up here.
</p>

<?php include 'foot.php'; ?>
