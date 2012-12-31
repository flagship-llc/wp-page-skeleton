<?php
// Admin page.
?>
<h1>Page Skeleton</h1>
<?php
if (!$wp_page_skeleton->enabled):
?>
<h2>Sorry, Page Skeleton is not enabled.</h2>
<p>Please make the <code>skeleton.yml</code> file in the root of your theme directory. Use the <code>skeleton.yml</code> file as a reference.</p>
<?php
else:
?>

<?php
if (wp_verify_nonce($_POST['_wpnonce'], 'wp_page_skeleton_sync')) {
  $action = true;
} else {
  $action = false;
}

$wp_page_skeleton->sync($action);
?>

<table class="wp-list-table widefat fixed">
  <thead>
    <tr>
      <th>Slug</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($wp_page_skeleton->pages_to_update as $p): ?>
    <tr>
      <td>
        <?php if (array_key_exists('page', $p)): ?>
        <a href="<?php echo get_permalink($p['page']); ?>">
        <?php endif; ?>
          <?php echo $p['slug']; ?>
        <?php if (array_key_exists('page', $p)): ?>
        </a>
        <?php endif; ?>
      </td>
      <td><?php echo $action ? 'done:' : 'will:' ?> <?php echo $p['action']; ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<form action="" method="POST">
  <?php wp_nonce_field( 'wp_page_skeleton_sync' ); ?>
  <input type="submit" value="Sync pages from Skeleton" class="button-primary" />
</form>

<?php
endif;
?>
