<?php
// Admin page.

$sync_label = 'Sync pages from Skeleton';
?>
<div id="page_skeleton">

<h1>Page Skeleton</h1>
<?php
if (!$wp_page_skeleton->enabled):
?>
<h2>Sorry, Page Skeleton is not enabled.</h2>
<p>Please make the <code>skeleton.yml</code> file in the root of your theme directory. Use the <code>skeleton.yml</code> file as a reference.</p>
<p>Or, do you want to <a href="admin.php?page=wp_page_skeleton_generate">generate it</a>?</p>
<?php
else:
?>

<?php
$action = false;

if (wp_verify_nonce($_POST['_wpnonce'], 'wp_page_skeleton_sync')) {
  if ($_POST['act'] == $sync_label) {
    $action = true;
  }
}

$wp_page_skeleton->sync($action);
?>

<table class="wp-list-table widefat fixed">
  <thead>
    <tr>
      <th>Slug</th>
      <th>Action</th>
      <th>Page Template</th>
      <th>Page Title</th>
      <th>Update Contents?</th>
    </tr>
  </thead>
  <tbody class="<?php echo $action ? 'action' : 'no-action ' ?> <?php echo $compare ? 'comparison' : 'no-comparison'; ?>">
    <?php foreach ($wp_page_skeleton->pages_to_update as $p): ?>
    <tr>
      <td>
        <?php if (array_key_exists('page', $p)): ?>
        <a href="<?php echo get_permalink($p['page']); ?>">
        <?php endif; ?>
          <?php echo urldecode($p['slug']); ?>
        <?php if (array_key_exists('page', $p)): ?>
        </a>
        <?php endif; ?>
      </td>
      <td><?php echo $action ? '' : 'will ' ?> <?php echo $p['action']; ?></td>
      <td><?php echo array_key_exists('template', $p['data']) ? $p['data']['template'] : 'default'; ?></td>
      <td><?php echo array_key_exists('title', $p['data']) ? $p['data']['title'] : '(unset)'; ?></td>
      <td><?php echo array_key_exists('content', $p['data']) ? '✓' : '×'; ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<form action="" method="POST">
  <?php wp_nonce_field( 'wp_page_skeleton_sync' ); ?>
  <div class="button-wrapper">
    <input type="submit" name="act" value="<?php echo $sync_label; ?>" class="button-primary" />
  </div>
</form>

<?php
endif;
?>

</div>
