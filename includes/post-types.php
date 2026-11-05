<?php

function create_company_resources_post_type(){
  register_post_type('company_resources', array(
    'labels'        => array(
      'name'          => __('Company Resources', 'company-resources-plugin'),
      'singular_name' => __('Company Resource', 'company-resources-plugin'),
      'menu_name'     => __('Company Resources', 'company-resources-plugin'),
      'add_new'       => __('Add New', 'company-resources-plugin'),
      'add_new_item'  => __('Add New Company Resource', 'company-resources-plugin'),
      'new_item'      => __('New Company Resource', 'company-resources-plugin'),
      'edit_item'     => __('Edit Company Resource', 'company-resources-plugin'),
      'view_item'     => __('View Company Resource', 'company-resources-plugin'),
      'all_items'     => __('All Company Resources', 'company-resources-plugin'),
    ),
    'public'        => true,
    'has_archive'   => true,
    'show_in_rest'  => true,
    'menu_icon'     => 'dashicons-media-document',
    'rewrite'       => array('slug' => 'company-resources'),
    'supports'      => array('title', 'editor', 'thumbnail', 'excerpt'),

  ));
}

add_action('init', 'create_company_resources_post_type');