<?php

/*
 * Item Metadata to files
 *
 */

class ItemMetastoFilesPlugin extends Omeka_Plugin_AbstractPlugin
{

  protected $_hooks = array(
  	'items_batch_edit_custom',
    'admin_items_batch_edit_form',
  );

    /**
     * Add custom fields to the item batch edit form.
     */
  public function hookAdminItemsBatchEditForm()
  {
    $view = get_view();
    $db = get_db();
  ?>
<fieldset id="item-metadata-to-file">
    <h2><?php echo __('Item metadata to files'); ?></h2>

<?php $metas = $db->query("SELECT e.id, e.name, s.name setname FROM `$db->Elements` e LEFT JOIN `$db->ElementSets` s ON s.id = e.element_set_id WHERE s.name = 'Item Type Metadata' OR s.name = 'Dublin Core' ORDER BY s.id, e.name")->fetchAll();
  foreach ($metas as $i => $meta) {
?>
        <div class="inputs five columns omega">
            <?php echo $view->formCheckbox(
                'custom[itemmetatofiles][' . $meta['id'] . ']',
                null,
                array(
                    'checked' => false,
                    'class' => 'item-metadata-to-file-checkbox',
            )); ?>
            <span class="explanation">
              <?php echo '[' . $meta['setname'] . '] - ' .  __($meta['name']); ?>
            </span>
        </div>
<?php } ?>

</fieldset>
<?php
  }

  function hookItemsBatchEditCustom ($args)
    {
      $item = $args['item'];
      $custom = $args['custom'];
      $db = get_db();
      $fids = $db->query("SELECT id FROM `$db->Files` WHERE item_id = " . $item->id)->fetchAll();
      foreach ($args['custom']['itemmetatofiles'] as $element_id => $yes) {
        if ($yes) {
          $element = $db->query("SELECT e.name, s.name setname FROM `$db->Elements` e LEFT JOIN `$db->ElementSets` s ON s.id = e.element_set_id WHERE e.id = " . $element_id)->fetchObject();
          $meta_value = metadata($item, [$element->setname, $element->name]);
          $meta_value = $db->quote($meta_value);
          foreach ($fids as $i => $fid) {
            $db->query('INSERT INTO omeka_element_texts (record_id, record_type, element_id, html, text) VALUES (' . $fid['id'] . ', \'File\', ' . $element_id . ', 0, ' . $meta_value . ')')->fetchAll();
          }
        }
      }
    }
}