

<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <input type="hidden" name="action" value="my_crud_form_submit">
    <?php wp_nonce_field('my_crud_form_submit', 'my_crud_nonce'); ?>
    <table class="form-table">
        <?php if (isset($item) && $item['id']) : ?>
            <input type="hidden" name="id" value="<?php echo esc_attr($item['id']); ?>">
        <?php endif; ?>
        <tr>
            <th scope="row">
                <label for="name">Name</label>
            </th>
            <td>
                <input type="text" name="name" id="name" class="regular-text" value="<?php echo esc_attr($item ? $item['name'] : ''); ?>">
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="description">Description</label>
            </th>
            <td>
                <textarea name="description" id="description" class="regular-text"><?php echo esc_textarea($item ? stripslashes($item['description']): ''); ?></textarea>
            </td>
        </tr>
    </table>
    <input type="submit" class="button button-primary" value="<?php echo $id ? 'Update' : 'Save'; ?>">
</form>
