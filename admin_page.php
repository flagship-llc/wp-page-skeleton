<?php
// Admin page.
?>
<h1>Skeleton</h1>
<?php
if (!$wp_page_skeleton->enabled):
?>
<h2>Sorry, Skeleton is not enabled.</h2>
<p>Please make the "skeleton.yml" file in the root of your theme directory. Here is a sample structure:</p>
<pre>
pages:
  parent_slug:
    title: I am the Parent
    content: This is some content.
    template: use-this-template.php
    pages:
      child_1:
        title: I am child 1
        template: child_1.php
      child_2:
        title: I am child 2
        template: child_2.php
</pre>
<?php
else:
?>

<pre><?php
if (wp_verify_nonce($_POST['_wpnonce'], 'wp_page_skeleton_sync')) {
  $wp_page_skeleton->sync(true);  
} else {
  $wp_page_skeleton->sync(false);
}
?></pre>

<form action="" method="POST">
  <?php wp_nonce_field( 'wp_page_skeleton_sync' ); ?>
  <input type="submit" value="Sync pages from Skeleton" class="button-primary" />
</form>

<?php
endif;
?>
