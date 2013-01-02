<?php
// Admin generate page
?>
<div id="page_skeleton">

<h1>Page Skeleton</h1>

<?php
if (wp_verify_nonce($_POST['_wpnonce'], 'wp_page_skeleton_generate')) {
  $include_content = false; $write_to_file = false;
  if (@$_POST['include_content'] == 'yes') {
    $include_content = true;
  }
  if (@$_POST['write_to_file'] == 'yes') {
    $write_to_file = true;
  }

  $error = false;
  $skel = $wp_page_skeleton->generate($error, $include_content, $write_to_file);
  if ($error == 'file permission') {
    $out = $skel;
    ?>
    <p>Warning: <code>skeleton.yml</code> was unable to be opened for writing (permissions?). Here are the contents:</p>
    <?php
  }
  if (!$write_to_file) {
    $out = $skel;
  }
}

if (isset($out)) {
  ?><pre><?php echo $out; ?></pre><?php
}
?>

<?php if ($wp_page_skeleton->enabled) {
?>
<p>Warning: <code>skeleton.yml</code> already exists. Setting the generator tool to write to the file will overwrite this.</p>
<?php
}
?>

<form action="" method="POST">
  <?php wp_nonce_field( 'wp_page_skeleton_generate' ); ?>
  <label for="include_content" class="block-label">
    <input type="checkbox" name="include_content" id="include_content" value="yes"> Include page content? (Page contents will <em>not</em> be overridden on sync if not checked)
  </label>
  <label for="write_to_file" class="block-label">
    <input type="checkbox" name="write_to_file" id="write_to_file" value="yes"> Write to file? (<?php echo $wp_page_skeleton->file; ?>)
  </label>
  <div class="button-wrapper">
    <input type="submit" value="Export current page structure to Skeleton" class="button-primary" />
  </div>
</form>

</div>
