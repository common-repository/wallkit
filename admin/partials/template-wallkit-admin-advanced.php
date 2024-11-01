<?php
/**
 * Wallkit advanced page
 *
 * @since      3.2.7
 * @package    Wallkit_Wp
 * @subpackage Wallkit_Wp/includes
 * @author     Wallkit <dev@wallkit.net>
 */
?>
<?php
//Get the active tab from the $_GET param
$default_tab                = 'sign-in';
$activeTab                  = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;

$tabs =     array(
    'sign-in' => array(
        'id'    => 'sign-in',
        'title' => 'Sign in'
    ),
    'popups' => array(
        'id'    => 'popups',
        'title' => 'Popups'
    )
);
?>
<div class="wrap">
    <!-- Print the page title -->
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <!-- Here are our tabs -->
    <nav class="nav-tab-wrapper">
        <?php
        foreach ($tabs as $tab) {
            printf('<a href="?page=wallkit-advanced&tab=%1$s" class="nav-tab %2$s">%3$s</a>',
                $tab['id'],
                ($activeTab == $tab['id'] ? 'nav-tab-active' : ''),
                $tab['title']);
        }
        ?>
    </nav>

    <div class="tab-content">
        <?php
        if( !empty($activeTab) && file_exists(WPWKP_TEMPLATE_DIR . '/advanced/template-' . $activeTab . '.php') ) {
            include_once  WPWKP_TEMPLATE_DIR . '/advanced/template-' . $activeTab . '.php';
        }
        ?>
    </div>
</div>