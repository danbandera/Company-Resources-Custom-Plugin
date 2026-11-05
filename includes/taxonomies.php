<?php

function register_company_resources_taxonomy() {
  register_taxonomy(
    'company_resource_tags',
    array('company_resources'),
    array(
      'labels' => array(
            'name'          => __('Resource Categories', 'company-resources-plugin'),
            'singular_name' => __('Resource Category', 'company-resources-plugin'),
        ),
      'hierarchical'      => true,
      'public'            => true,
      'show_ui'           => true,
      'show_admin_column' => true,
      'show_in_rest'      => true,
      'rewrite'      => array('slug' => 'resource-category'),
    ),
  );
}

add_action('init', 'register_company_resources_taxonomy');