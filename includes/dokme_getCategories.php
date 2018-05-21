<?php

class dokme_getCategories
{

    public static function getStoreCategories()
    {
        $args = array(
            'taxonomy' => 'product_cat',
            'show_count' => false,
            'pad_counts' => false,
            'hierarchical' => true,
            'title_li' => '',
            'hide_empty' => false
        );
        $storeCategories = json_decode(json_encode(get_categories($args)), true);
        return static::buildTree($storeCategories);
    }

    public static function buildTree(array $elements, $parentId = 0)
    {
        $branch = array();
        foreach ($elements as $element) {
            if ($element['parent'] == $parentId) {
                $children = static::buildTree($elements, $element['cat_ID']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }

    public static function traverse($categories)
    {
        $selectedCategories = get_site_option('DOKME_SELECTED_CATEGORIES');

        $out = '<ul>';
        foreach ($categories as $category) {
            $checked = '';
            if (!empty($selectedCategories)) {
                $checked = in_array($category['term_id'], $selectedCategories) ? 'checked' : '';
            }

            $hasChildren = !empty($category['children']);

            $out .= sprintf('<li><input type="checkbox" value="%d" %s><i class=collapse></i><span class=collapse>%s</span>', $category['term_id'], $checked, $category['name']);

            if ($hasChildren) {
                $out .= static::traverse($category['children']);
            }
            $out .= '</li>';
        }

        $out .= '</ul>';

        return $out;
    }

}
