<?php

class DokmeApiCategories
{
    public static function getCategories()
    {
        $args = array(
            'taxonomy' => 'product_cat',
            'orderby' => 'name',
            'show_count' => 0,
            'pad_counts' => 0,
            'hierarchical' => 1,
            'title_li' => '',
            'hide_empty' => 0
        );
        $categories = get_categories($args);

        $data = array();
        foreach ($categories as $category) {
            $data[] = array(
                'id' => $category->cat_ID,
                'parent_id' => $category->parent,
                'name' => $category->name
            );
        }

        echo(json_encode(
            array(
                'status' => true,
                'data' => $data
            )
        ));
    }
}
