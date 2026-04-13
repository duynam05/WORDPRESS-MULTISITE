<?php
/*
Plugin Name: Network Site Stats
Description: Thống kê các site trong Multisite
Version: 1.1
Author: Trịnh Duy Nam
Network: true
*/

// Thêm menu
add_action('network_admin_menu', 'nss_add_menu');

function nss_add_menu() {
    add_menu_page(
        'Network Stats',
        'Network Stats',
        'manage_network',
        'network-site-stats',
        'nss_render_page'
    );
}

// Hàm tính dung lượng
function nss_get_dir_size($dir) {
    $size = 0;
    foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $each) {
        $size += is_file($each) ? filesize($each) : nss_get_dir_size($each);
    }
    return $size;
}

// Hiển thị trang
function nss_render_page() {

    if (!current_user_can('manage_network')) {
        return;
    }

    ?>
    <div class="wrap">
        <h1>Network Site Stats</h1>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Site Name</th>
                    <th>Post Count</th>
                    <th>Latest Post</th>
                    <th>Storage</th>
                </tr>
            </thead>
            <tbody>
    <?php

    $sites = get_sites();

    foreach ($sites as $site) {

        switch_to_blog($site->blog_id);

        $name = get_bloginfo('name');

        $post_count = wp_count_posts()->publish;

        $latest = get_posts(['numberposts' => 1]);
        $latest_date = $latest ? $latest[0]->post_date : 'N/A';

        // 👉 Lấy dung lượng đúng cho từng site
        $upload_dir = wp_upload_dir();
        $size = size_format(nss_get_dir_size($upload_dir['basedir']));

        echo "<tr>
                <td>{$site->blog_id}</td>
                <td>{$name}</td>
                <td>{$post_count}</td>
                <td>{$latest_date}</td>
                <td>{$size}</td>
              </tr>";

        restore_current_blog();
    }

    ?>
            </tbody>
        </table>
    </div>
    <?php
}