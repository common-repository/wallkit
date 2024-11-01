<?php
/**
 * Wallkit settings page
 *
 * @since      1.1.17
 * @package    Wallkit_Wp
 * @subpackage Wallkit_Wp/includes
 * @author     Wallkit <dev@wallkit.net>
 */
?>
<?php
//Get the active tab from the $_GET param
$default_tab                = 'settings';
$activeTab                  = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;
$devTab                     = isset($_GET['wallkit']) && $_GET['wallkit'] === 'dev';
$wk_custom_integration      = isset($settings) ? $settings->get_option("wk_custom_integration") : false;

$tabs = array(
    'settings' => array(
        'id'    => 'settings',
        'title' => 'Settings'
    )
);

if( $devTab ) {
    $tabs = array_merge( $tabs,
        array(
            'dev-settings' => array(
                'id'    => 'dev-settings',
                'title' => 'Dev Settings'
            )
        )
    );
}

if( !$wk_custom_integration ) {
    $tabs = array_merge( $tabs,
        array(
            'integration-settings' => array(
                'id'    => 'integration-settings',
                'title' => 'Integration Settings'
            ),
            'tools' => array(
                'id'    => 'wallkit-calls',
                'title' => 'Wallkit Calls'
            ),
            'additional-script' => array(
                'id'    => 'additional-script',
                'title' => 'Additional Integration Script'
            ),
        )
    );
}
?>
<div class="wrap">
    <!-- Print the page title -->
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <!-- Here are our tabs -->
    <nav class="nav-tab-wrapper">
        <?php
        foreach ($tabs as $tab) {
            printf('<a href="?page=wallkit-settings&tab=%1$s%2$s" class="nav-tab %3$s">%4$s</a>',
                $tab['id'],
                $devTab ? '&wallkit=dev' : '',
                ($activeTab == $tab['id'] ? 'nav-tab-active' : ''),
                $tab['title']);
        }
        ?>
    </nav>

    <div class="tab-content">
        <?php
        if( !empty($activeTab) && file_exists(WPWKP_TEMPLATE_DIR . '/settings/template-' . $activeTab . '.php') ) {
            include_once  WPWKP_TEMPLATE_DIR . '/settings/template-' . $activeTab . '.php';
        }
        ?>
    </div>
</div>
