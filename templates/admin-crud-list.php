<div class="wrap">
    <h1 class="wp-heading-inline">My CRUD Entries</h1>
    <a href="?page=my-crud-plugin&action=add" class="page-title-action">Add New</a>

    <form method="post">
        <?php
        $table = new My_CRUD_List_Table();
        $table->prepare_items();
        $table->display();
        ?>
    </form>
</div>
