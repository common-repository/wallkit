<?php
/**
 * @package    Wallkit_Wp
 * @subpackage Wallkit_Wp/admin
 * @author     Wallkit <dev@wallkit.net>
 */

class Wallkit_Wp_Settings {

    /**
     * Option name in wp options
     */
    const SETTINGS_SLUG = 'WALLKIT';

    /**
     * Option name in wp options
     */
    const TASK_SLUG = 'wk_tasks';

    /**
     * @var string
     */
    private $plugin_name;

    /**
     * @var string
     */
    private $version;

    /**
     * @var bool
     */
    private $task = false;

    /**
     * @var string
     */
    private $plugin_title;

    /**
     * @var Wallkit_Wp_Resource_Settings
     */
    private $resource_settings;

    /**
     * @var \WallkitSDK\WallkitSDK
     */
    private $wallkitSDK;

    /**
     * Wallkit_Wp_Settings constructor.
     *
     * @param $plugin_name
     * @param $version
     * @param $plugin_title
     */
    function __construct($plugin_name, $version, $plugin_title) {

        $this->version = $version;
        $this->plugin_name = $plugin_name;
        $this->plugin_title = $plugin_title;
        $this->setup_wallkit_sdk();
        $this->setup_resource_settings();
    }

    /**
     * Default settings for option
     */
    const default_settings = [
        "wk_r_key"                  => null,
        "wk_rs_key"                 => null,
        "wk_server"                 => 'prod',
        "wk_wilmode"                => 'prod',
        "wk_debug"                  => false,
        "wk_is_active"              => true,
        "wk_is_auto_sync"           => true,
        "wk_check_taxonomies_sync"  => [],
        "wk_check_post_access"      => false,
        "wk_check_post_type_access" => [],
        "wk_admin_paywall"          => false,
        "wk_free_paragraph"         => 1,
        "wk_show_blur"              => true,
        "wk_paywall_display_type"   => 0,
        "wk_content_key_prefix"     => null,
        "wk_content_class_selector" => 'wkwp-post-content',
        "wk_custom_content_selector"=> null,
        "wk_custom_integration"     => false,
        "wk_analytics"              => false,
        "wk_sign_in_button"         => true,
        "wk_reload_on_logout"       => true,
        "wk_nav_menu_sign_in_button"=> [],
        "wk_content_access_html"    => 'ICAgICAgICA8ZGl2IGNsYXNzPSJ3YWxsa2l0LXBheXdhbGwtYmxvY2siIHN0eWxlPSJkaXNwbGF5OiBibG9jazsiPg0KICAgICAgICAgICAgPGgzIGNsYXNzPSJ3YWxsa2l0LXBheXdhbGwtYmxvY2tfX3RpdGxlIj5Db250aW51ZSByZWFkaW5nPC9oMz4NCiAgICAgICAgICAgIDxwIGNsYXNzPSJ3YWxsa2l0LXBheXdhbGwtYmxvY2tfX2Rlc2NyaXB0aW9uIj5TdWJzY3JpYmUgZm9yIEV4Y2x1c2l2ZSBDb250ZW50LCBGdWxsIFZpZGVvIEFjY2VzcywgUHJlbWl1bSBFdmVudHMsIGFuZCBNb3JlITwvcD4NCiAgICAgICAgICAgIDxhIGhyZWY9IiMiIGNsYXNzPSJ3YWxsa2l0LXN1YnNjcmliZS1idG4gd2stY2FsbCB3a+KAk3BsYW5zIj5TdWJzY3JpYmU8L2E+DQogICAgICAgICAgICA8cCBjbGFzcz0id2FsbGtpdC1wYXl3YWxsLWJsb2NrX19sb2dpbl9wbGFucyB3YWxsa2l0LXN1YnNjcmliZS1wbGFuLWN0YSI+QWxyZWFkeSBhIHN1YnNjcmliZXI/IDxhIGhyZWY9IiMiIGNsYXNzPSJ3ay1jYWxsIHdr4oCTc2lnbi1pbiI+TG9naW48L2E+PC9wPg0KICAgICAgICA8L2Rpdj4=',
        "wk_paywall_styles"         => 'LyogU3RhcnQgRnJvbnRlbmQgcGF5d2FsbGVkICovDQoud2t3cC1wb3N0LWNvbnRlbnQgLndrd3AtYmx1ciB7DQoJZGlzcGxheTpibG9jazsNCiAgICBmaWx0ZXI6Ymx1cig0cHgpOw0KCS13ZWJraXQtdXNlci1zZWxlY3Q6IG5vbmU7DQoJLW1zLXVzZXItc2VsZWN0OiBub25lOw0KCXVzZXItc2VsZWN0OiBub25lOw0KfQ0KDQoud2t3cC1wb3N0LWNvbnRlbnQgLndrd3Atbm9uLWJsdXIgew0KCWRpc3BsYXk6IG5vbmUgIWltcG9ydGFudDsNCn0NCi8qIEVuZCBGcm9udGVuZCBwYXl3YWxsZWQgKi8NCg0KLndrd3AtcGF5d2FsbCBhIHsNCiAgICAgICAgCWJveC1zaGFkb3c6IG5vbmU7DQogICAgICAgIH0NCiAgICAgICAgDQogICAgICAgIC53a3dwLXBheXdhbGwgLndrd3AtY29udGVudC1pbm5lciB7DQogICAgICAgIAlkaXNwbGF5Om5vbmU7DQogICAgICAgIH0NCiAgICAgICAgLndrd3AtcGF5d2FsbCAud2t3cC1jb250ZW50LWlubmVyLndrd3AtY29udGVudC1ibHVyZWQgew0KICAgICAgICAJZGlzcGxheTpibG9jazsNCiAgICAgICAgCWZpbHRlcjpibHVyKDRweCk7DQoJCQktd2Via2l0LXVzZXItc2VsZWN0OiBub25lOw0KCQkJLW1zLXVzZXItc2VsZWN0OiBub25lOw0KCQkJdXNlci1zZWxlY3Q6IG5vbmU7DQogICAgICAgIH0NCiAgICAgICAgDQogICAgICAgIC53a3dwLXBheXdhbGwgLndrd3AtcGF5d2FsbC1ibG9jayB7DQogICAgICAgICAgICBwb3NpdGlvbjogcmVsYXRpdmU7DQogICAgICAgIH0NCiAgICAgICAgLndrd3AtcGF5d2FsbCAud2t3cC1wYXl3YWxsLWJsb2NrOmJlZm9yZSB7DQogICAgICAgICAgICBjb250ZW50OiAnJzsNCiAgICAgICAgICAgIGRpc3BsYXk6IGJsb2NrOw0KICAgICAgICAgICAgd2lkdGg6IDEwMCU7DQogICAgICAgICAgICBoZWlnaHQ6IDE0MHB4Ow0KICAgICAgICAgICAgcG9zaXRpb246IGFic29sdXRlOw0KICAgICAgICAgICAgYmFja2dyb3VuZDogbGluZWFyLWdyYWRpZW50KHJnYmEoMjU1LCAyNTUsIDI1NSwgMCksIHJnYmEoMjU1LCAyNTUsIDI1NSwgMC44KSwgd2hpdGUpOw0KICAgICAgICAgICAgbGVmdDogMDsNCiAgICAgICAgCXRvcDogLTEzMHB4Ow0KICAgICAgICB9DQogICAgICAgIA0KICAgICAgICAud2t3cC1wYXl3YWxsLWJsb2NrIC53YWxsa2l0LXBheXdhbGwtYmxvY2sgew0KICAgICAgICAJcG9zaXRpb246IHJlbGF0aXZlOw0KICAgICAgICAgICAgdGV4dC1hbGlnbjogY2VudGVyOw0KICAgICAgICAgICAgbWFyZ2luOiAwIGF1dG87DQogICAgICAgICAgICBiYWNrZ3JvdW5kLWNvbG9yOiAjZmZmZmZmOw0KICAgICAgICAgICAgYm9yZGVyOiAxcHggc29saWQgI0FBMDAwMDsNCiAgICAgICAgICAgIHBhZGRpbmc6IDQwcHggMjBweDsNCiAgICAgICAgfQ0KICAgICAgICANCiAgICAgICAgLndrd3AtcGF5d2FsbC1ibG9jayAud2FsbGtpdC1wYXl3YWxsLWJsb2NrIC53YWxsa2l0LXBheXdhbGwtYmxvY2tfX3RpdGxlIHsNCiAgICAgICAgICAgIGNvbG9yOiAjQUEwMDAwOw0KICAgICAgICAgICAgZm9udC1zaXplOiAyNHB4Ow0KCQkJbGluZS1oZWlnaHQ6IDM2cHg7DQogICAgICAgICAgICBtYXJnaW46IDA7DQogICAgICAgICAgICBtYXJnaW4tYm90dG9tOiAxNnB4Ow0KCQkJdGV4dC1hbGlnbjogY2VudGVyOw0KCQkJdGV4dC10cmFuc2Zvcm06IHVwcGVyY2FzZTsNCgkJCWZvbnQtZmFtaWx5OiAnT3N3YWxkJywgc2Fucy1zZXJpZjsNCiAgICAgICAgfQ0KICAgICAgICANCiAgICAgICAgLndrd3AtcGF5d2FsbC1ibG9jayAud2FsbGtpdC1wYXl3YWxsLWJsb2NrIC53YWxsa2l0LXBheXdhbGwtYmxvY2tfX2Rlc2NyaXB0aW9uIHsNCiAgICAgICAgICAgIGZvbnQtc2l6ZTogMThweDsNCiAgICAgICAgICAgIG1hcmdpbi1ib3R0b206IDE4cHg7DQogICAgICAgICAgICBsaW5lLWhlaWdodDogMjRweDsNCiAgICAgICAgICAgIGNvbG9yOiAjMzMzMzMzOw0KCQkJdGV4dC1hbGlnbjogY2VudGVyOw0KCQkJZm9udC1mYW1pbHk6ICdJbnRlcicsIHNhbnMtc2VyaWY7DQogICAgICAgIH0NCiAgICAgICAgDQogICAgICAgIC53a3dwLXBheXdhbGwtYmxvY2sgLndhbGxraXQtcGF5d2FsbC1ibG9jayAud2FsbGtpdC1zdWJzY3JpYmUtYnRuIHsNCiAgICAgICAgCXRleHQtZGVjb3JhdGlvbjogbm9uZTsNCiAgICAgICAgICAgIGJhY2tncm91bmQ6ICNBQTAwMDA7DQoJCQlib3JkZXI6IDFweCBzb2xpZCAjQUEwMDAwOw0KICAgICAgICAgICAgY29sb3I6ICNmZmZmZmY7DQoJCQl0ZXh0LXRyYW5zZm9ybTogdXBwZXJjYXNlOw0KICAgICAgICAgICAgcGFkZGluZzogMTBweCA0MHB4Ow0KCQkJZm9udC1mYW1pbHk6ICdJbnRlcicsIHNhbnMtc2VyaWY7CQkJDQogICAgICAgICAgICBkaXNwbGF5OiBpbmxpbmUtYmxvY2s7DQogICAgICAgICAgICBmb250LXNpemU6IDE2cHg7DQoJCQlsaW5lLWhlaWdodDogMThweDsNCiAgICAgICAgICAgIG1hcmdpbjogMCAwIDE1cHg7DQogICAgICAgICAgICB6LWluZGV4OiAxOw0KICAgICAgICAgICAgcG9zaXRpb246IHJlbGF0aXZlOw0KICAgICAgICAgICAgYm94LXNoYWRvdzogbm9uZTsNCgkJCXRleHQtYWxpZ246IGNlbnRlcjsNCgkJCS13ZWJraXQtdHJhbnNpdGlvbjogYWxsIGVhc2UtaW4tb3V0IC4zczsNCiAgCQkJLW1vei10cmFuc2l0aW9uOiBhbGwgZWFzZS1pbi1vdXQgLjNzOw0KICAJCQktbXMtdHJhbnNpdGlvbjogYWxsIGVhc2UtaW4tb3V0IC4zczsNCiAgCQkJLW8tdHJhbnNpdGlvbjogYWxsIGVhc2UtaW4tb3V0IC4zczsNCiAgCQkJdHJhbnNpdGlvbjogYWxsIGVhc2UtaW4tb3V0IC4zczsNCiAgICAgICAgfQ0KDQoJCS53a3dwLXBheXdhbGwtYmxvY2sgLndhbGxraXQtcGF5d2FsbC1ibG9jayAud2FsbGtpdC1zdWJzY3JpYmUtYnRuOmhvdmVyIHsNCgkJCWJhY2tncm91bmQ6ICNmZmZmZmY7DQoJCQljb2xvcjogIzAwMDAwMDsNCgkJfQ0KDQoJCS53a3dwLXBheXdhbGwtYmxvY2sgLndhbGxraXQtcGF5d2FsbC1ibG9jayAud2FsbGtpdC1zdWJzY3JpYmUtcGxhbi1jdGEgew0KCQkJdGV4dC1hbGlnbjogY2VudGVyOw0KCQkJZm9udC1mYW1pbHk6ICdJbnRlcicsIHNhbnMtc2VyaWY7DQoJCQlmb250LXNpemU6IDE0cHg7DQoJCQlsaW5lLWhlaWdodDogMTdweDsNCgkJCWNvbG9yOiAjMDAwMDAwOw0KCQl9DQogICAgICAgIA0KICAgICAgICAud2t3cC1wYXl3YWxsLWJsb2NrIC53YWxsa2l0LXBheXdhbGwtYmxvY2sgLndhbGxraXQtc3Vic2NyaWJlLXBsYW4tY3RhIC53YWxsa2l0LXBheXdhbGwtYmxvY2tfX2xvZ2luX3BsYW5zIHsNCgkJCWZvbnQtZmFtaWx5OiAnSW50ZXInLCBzYW5zLXNlcmlmOw0KCQkJZm9udC1zaXplOiAxNHB4Ow0KCQkJbGluZS1oZWlnaHQ6IDE3cHg7DQogICAgICAgICAgICBjb2xvcjogI0FBMDAwMDsNCiAgICAgICAgCWJveC1zaGFkb3c6IG5vbmU7CQ0KICAgICAgICB9DQoNCgkJ',
        "wk_my_account_html"        => 'ICAgICAgICA8ZGl2IGNsYXNzPSJ3ay1sb2dpbi1jb250YWluZXIgd2stbG9naW4tc3RpY2t5Ij4NCiAgICAgICAgICAgIDxkaXYgY2xhc3M9IndrLWxvZ2luLXdyYXBwZXIiPg0KICAgICAgICAgICAgICAgIDxkaXYgY2xhc3M9IndrLXVzZXIiPg0KICAgICAgICAgICAgICAgICAgICBbd2tfc2l0ZV9sb2dvXQ0KICAgICAgICAgICAgICAgICAgICBbd2tfbXlfYWNjb3VudF9idXR0b25dDQogICAgICAgICAgICAgICAgICAgIFt3a19teV9hY2NvdW50X2ltZ10gICAgICAgICAgICAgICAgIA0KICAgICAgICAgICAgICAgIDwvZGl2Pg0KICAgICAgICAgICAgPC9kaXY+DQogICAgICAgIDwvZGl2Pg==',
        "wk_my_account_styles"      => 'Lndrd3AtdXNlci1oaWRlIC53a3dwLXVzZXItbXktYWNjb3VudC1idXR0b24gew0KCWRpc3BsYXk6IG5vbmUgIWltcG9ydGFudDsgDQp9DQoNCi53a3dwLXVzZXItaGlkZSAud2t3cC1sb2dpbi1ibG9jayB7DQoJZGlzcGxheTogbm9uZSAhaW1wb3J0YW50Ow0KfQ0KLndrLWxvZ2luLWNvbnRhaW5lci53ay1sb2dpbi1zdGlja3l7DQogICAgICAgIAliYWNrZ3JvdW5kOiNmZmZmZmY7DQogICAgICAgIAlwb3NpdGlvbjpmaXhlZDsNCiAgICAgICAgCWJvdHRvbToyMHB4Ow0KICAgICAgICAJcmlnaHQ6MjBweDsNCiAgICAgICAgCXBhZGRpbmc6MjBweCAzM3B4Ow0KICAgICAgICAJei1pbmRleDogMTAwOw0KCQkJYm9yZGVyOiAxcHggc29saWQgIzAwMDAwMDsNCgkgICAgICAgIC13ZWJraXQtdHJhbnNpdGlvbjogYm90dG9tIGN1YmljLWJlemllcigwLjQsIDAsIDAuMiwgMSkgLjdzOw0KICAJCQktbW96LXRyYW5zaXRpb246IGJvdHRvbSBjdWJpYy1iZXppZXIoMC40LCAwLCAwLjIsIDEpIC43czsNCiAgCQkJLW1zLXRyYW5zaXRpb246IGJvdHRvbSBjdWJpYy1iZXppZXIoMC40LCAwLCAwLjIsIDEpIC43czsNCiAgCQkJLW8tdHJhbnNpdGlvbjogYm90dG9tIGN1YmljLWJlemllcigwLjQsIDAsIDAuMiwgMSkgLjdzOw0KICAJCQl0cmFuc2l0aW9uOiBib3R0b20gY3ViaWMtYmV6aWVyKDAuNCwgMCwgMC4yLCAxKSAuN3M7DQoJICAgICAgICBib3R0b206IC0yMDBweDsNCiAgICAgICAgfQ0KDQogICAgICAgIC53a3dwLWxvZ2luLWJsb2NrIC53ay1sb2dpbi1jb250YWluZXIgLndrLWxvZ2luLXdyYXBwZXIgLndrLXVzZXIgLndrd3AtdXNlci1teS1hY2NvdW50LWltZyB7DQoJCQl3aWR0aDogMjRweDsNCgkJCWhlaWdodDogMjRweDsNCgkJCWJvcmRlci1yYWRpdXM6IDEwMDBweDsNCgkJCW1hcmdpbi1yaWdodDogOHB4Ow0KCQkJZGlzcGxheTogbm9uZTsNCiAgICAgICAgfQ0KDQogICAgICAgIC53a3dwLWxvZ2luLWJsb2NrW2RhdGEtd2stY2FsbC1zdGF0dXMtdXNlcj0iZ3Vlc3QiXSAud2stbG9naW4tY29udGFpbmVyLndrLWxvZ2luLXN0aWNreSB7DQoJCQlib3R0b206IDMwcHg7DQogICAgICAgIH0NCiAgICAgICAgLndrd3AtbG9naW4tYmxvY2tbZGF0YS13ay1jYWxsLXN0YXR1cy11c2VyPSJhdXRob3JpemVkIl0gLndrLWxvZ2luLWNvbnRhaW5lci53ay1sb2dpbi1zdGlja3kgew0KCQkJYm90dG9tOiAzMHB4Ow0KICAgICAgICB9DQogICAgICAgIC53a3dwLWxvZ2luLWJsb2NrW2RhdGEtd2stY2FsbC1zdGF0dXMtdXNlcj0iZ3Vlc3QiXSAud2stbG9naW4tY29udGFpbmVyIC53ay1sb2dpbi13cmFwcGVyIC53ay11c2Vyew0KICAgICAgICAJZGlzcGxheTpmbGV4Ow0KICAgICAgICAJYWxpZ24taXRlbXM6Y2VudGVyOw0KCQkJZmxleC1kaXJlY3Rpb246IGNvbHVtbjsNCiAgICAgICAgfQ0KDQogICAgICAgIC53a3dwLWxvZ2luLWJsb2NrW2RhdGEtd2stY2FsbC1zdGF0dXMtdXNlcj0iZ3Vlc3QiXSAud2stbG9naW4tY29udGFpbmVyIC53ay1sb2dpbi13cmFwcGVyIC53ay11c2VyIGF7DQogICAgICAgIAlmb250LXNpemU6MTZweDsNCgkJCXRleHQtYWxpZ246IGNlbnRlcjsNCgkJCWRpc3BsYXk6IGJsb2NrOw0KCQkJZm9udC1mYW1pbHk6ICdJbnRlcicsIHNhbnMtc2VyaWY7DQoJCQljb2xvcjogIzAwMDAwMDsNCgkJCXBhZGRpbmc6IDEwcHggMjBweDsNCgkJCWJvcmRlcjogMXB4IHNvbGlkICNBQTAwMDA7DQoJCQl0ZXh0LWRlY29yYXRpb246IG5vbmU7DQoJCQl3aWR0aDogMTAwJTsNCgkJCWZvbnQtd2VpZ2h0OiA1MDA7DQoJCQktd2Via2l0LXRyYW5zaXRpb246IGFsbCBlYXNlLWluLW91dCAuM3M7DQogIAkJCS1tb3otdHJhbnNpdGlvbjogYWxsIGVhc2UtaW4tb3V0IC4zczsNCiAgCQkJLW1zLXRyYW5zaXRpb246IGFsbCBlYXNlLWluLW91dCAuM3M7DQogIAkJCS1vLXRyYW5zaXRpb246IGFsbCBlYXNlLWluLW91dCAuM3M7DQogIAkJCXRyYW5zaXRpb246IGFsbCBlYXNlLWluLW91dCAuM3M7DQogICAgICAgIH0NCgkJLndrd3AtbG9naW4tYmxvY2tbZGF0YS13ay1jYWxsLXN0YXR1cy11c2VyPSJndWVzdCJdIC53ay1sb2dpbi1jb250YWluZXIgLndrLWxvZ2luLXdyYXBwZXIgLndrLXVzZXIgYTpob3ZlciB7DQoJCQliYWNrZ3JvdW5kLWNvbG9yOiAjQUEwMDAwOw0KCQkJY29sb3I6ICNmZmZmZmY7DQoJCX0NCg0KCQkud2t3cC1sb2dpbi1ibG9ja1tkYXRhLXdrLWNhbGwtc3RhdHVzLXVzZXI9Imd1ZXN0Il0gLndrLWxvZ2luLWNvbnRhaW5lciAud2stbG9naW4td3JhcHBlciAud2stdXNlciB7DQoJCQltYXgtd2lkdGg6MTAwcHg7DQoJCX0NCg0KCQkud2t3cC1sb2dpbi1ibG9jayAud2stbG9naW4tY29udGFpbmVyIC53ay1sb2dpbi13cmFwcGVyIC53ay11c2VyIC53a3dwLXNpdGUtbG9nbyB7DQoJCQlkaXNwbGF5Om5vbmU7DQoJCQltYXgtd2lkdGg6IDEwMHB4Ow0KCQkJbWFyZ2luLWJvdHRvbTogMTVweDsNCgkJfQ0KDQoud2t3cC1sb2dpbi1ibG9ja1tkYXRhLXdrLWNhbGwtc3RhdHVzLXVzZXI9ImF1dGhvcml6ZWQiXSAud2stbG9naW4tY29udGFpbmVyIHsNCglwYWRkaW5nOiA4cHggMjJweDsNCn0NCi53a3dwLWxvZ2luLWJsb2NrW2RhdGEtd2stY2FsbC1zdGF0dXMtdXNlcj0iYXV0aG9yaXplZCJdIC53ay1sb2dpbi1jb250YWluZXIgLndrLXVzZXIgew0KCWZsZXgtZGlyZWN0aW9uOiByb3ctcmV2ZXJzZTsNCgltYXgtd2lkdGg6IGluaXRpYWw7DQoJZGlzcGxheTogZmxleDsNCglhbGlnbi1pdGVtczogY2VudGVyOw0KfQ0KDQoud2t3cC1sb2dpbi1ibG9ja1tkYXRhLXdrLWNhbGwtc3RhdHVzLXVzZXI9ImF1dGhvcml6ZWQiXSAud2stbG9naW4tY29udGFpbmVyIC53ay11c2VyIGEgew0KCXBhZGRpbmc6IDA7DQoJbWFyZ2luOiAwOw0KCWZvbnQtc2l6ZTogMTZweDsNCglsaW5lLWhlaWdodDogMTZweDsNCglmb250LWZhbWlseTogJ0ludGVyJywgc2Fucy1zZXJpZjsNCglib3JkZXI6IG5vbmU7CQ0KCWNvbG9yOiAjMDAwMDAwOw0KCXRleHQtZGVjb3JhdGlvbjogbm9uZTsNCn0NCi53a3dwLWxvZ2luLWJsb2NrW2RhdGEtd2stY2FsbC1zdGF0dXMtdXNlcj0iYXV0aG9yaXplZCJdIC53ay1sb2dpbi1jb250YWluZXIgLndrLXVzZXIgYTpob3ZlciB7DQoJYmFja2dyb3VuZC1jb2xvcjogdHJhbnNwYXJlbnQ7DQoJY29sb3I6ICMwMDAwMDA7DQp9',
        "wk_additional_options"     => null,
        "wk_show_real_paragraphs_ipsum" => true,
        "wk_calls_use"              => true,
        "wk_calls_debug"            => false,
        "wk_calls_handle_click"     => 'wk-call',
        "wk_calls_users_status"     => 'wk-call-status-user',
        "wk_calls_users_status_body"=> false,
        "wk_calls_users_plans"      => 'wk-call-status-plans',
        "wk_calls_users_plans_body" => false,
        "wk_calls_users_events"     => 'wk-call-status-events',
        "wk_calls_users_events_body"=> false,
        "wk_additional_script"      => null,
        "wk_additional_script_place"=> false,
        "wk_auth_migrated_users"    => false,
        "wk_auth_allow_empty_pass"  => false,
        "wk_auth_migrated_users_text"=> 'PHAgc3R5bGU9ImZvbnQtc2l6ZTogMjBwdCAhaW1wb3J0YW50OyBsaW5lLWhlaWdodDogMS4yICFpbXBvcnRhbnQ7Ij48Yj5XZSBoYXZlIHVwZ3JhZGVkIG91ciBtZW1iZXJzaGlwIHN5c3RlbTwvYj48YnI+UGxlYXNlIGNoZWNrIHlvdXIgbWFpbGJveCBmb3IgYSBzZWN1cmUgbGluayB0byBzaWduIGluIGFuZCBzZXQgdXAgYSBuZXcgcGFzc3dvcmQuPC9wPjxwIHN0eWxlPSIiPklmIHlvdSBoYXZlIGFueSBxdWVzdGlvbnMgb3IgbmVlZCBoZWxwLCBwbGVhc2UgZW1haWwgPGEgaHJlZj0ibWFpbHRvOmluZm9Ad2FsbGtpdC5jb20iPmluZm9Ad2FsbGtpdC5jb208L2E+PC9wPg==',
        "wk_modals_inline_selector" => '#wk-inline-popup-modal',
        "wk_my_account_page_url"    => null,
    ];

    /**
     * @return mixed
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * @return mixed
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * @return mixed
     */
    public function get_plugin_title() {
        return $this->plugin_title;
    }

    /**
     * @param $name
     * @param $value
     */
    public function update_option( $name, $value ) {
        $option = get_option( self::SETTINGS_SLUG );

        if($option && is_string($option))
        {
            $option = unserialize(wp_unslash($option));
        }

        $option = array_merge( self::default_settings, (array) $option, array( $name => $value ) );

        update_option( self::SETTINGS_SLUG, (string) serialize($option), false);
    }

    /**
     * checl active plugin for load tools
     * @return bool
     */
    public function is_active() {
        return (bool) $this->get_option("wk_is_active", false);
    }

    /**
     * @return array
     */
    public function get_default_settings() {
        return (array) self::default_settings;
    }

    /**
     *
     * @return string
     */
    public function get_site_lang() {
        $lang = get_bloginfo( "language" );
        if (!empty($lang)) {
            $lang = substr($lang, 0, 2);
        } else {
            $lang = 'en';
        }

        return apply_filters( 'wallkit_site_language', $lang);
    }

    /**
     * @return array
     */
    public static function get_options() {

        $settings = get_option( self::SETTINGS_SLUG );

        if($settings && is_string($settings))
        {
            $settings = unserialize(wp_unslash($settings));
        }

        return (array) array_merge(self::default_settings, (array) $settings);
    }

    /**
     * @param $key
     * @param bool $default
     * @return bool|mixed
     */
    public function get_option( $key, $default = NULL ) {

        $option = $this->get_options();

        if ( false === $option ) {
            return $default;
        }

        if(array_key_exists($key, $option))
        {
            return $option[$key];
        }

        return $default;
    }

    /**
     * @return bool|mixed
     */
    public function getPublicKey() {
        return $this->get_option("wk_r_key");
    }

    /**
     * Get taxonomies available for sync
     *
     * @return bool|mixed
     */
    public function getOptionSyncTaxonomies() {
        $wk_check_taxonomies_sync = $this->get_option('wk_check_taxonomies_sync');
        if( empty($wk_check_taxonomies_sync) ) {
            $taxonomies = get_taxonomies();
            foreach ($taxonomies as $taxonomy) {
                $wk_check_taxonomies_sync[$taxonomy] = 1;
            }
        }

        return apply_filters( 'wallkit_override_taxonomies_for_sync', $wk_check_taxonomies_sync);
    }

    /**
     * @return array
     */
    public function fresh_task() {

        $option = get_option( self::TASK_SLUG , $this->get_default_task());

        if($option && is_string($option))
        {
            $option = unserialize(wp_unslash($option));
        }

        $this->task = (array) $option;

        return $this->task;
    }

    /**
     * @param bool $_get_only
     * @return array
     */
    public function get_task($_get_only = false) {

        if(!$this->task)
        {
            $array = (array) $this->fresh_task();
        }
        else
        {
            $array = (array) $this->task;
        }


        if(!$_get_only && isset($array["last_time"]) && $array["last_time"] &&
            isset($array["status"]) &&
             !in_array($array["status"], ["stopped", "hold", "finished", "exception", "paused", "fail", "error", "broken"], true) &&
            $array["last_time"] <= (time() - 120
            )
        ) {
            $array = $this->update_task([
                "status" => "hold"
            ], true);
        }

        return $array;
    }

    /**
     * @return array
     */
    public function get_default_task() {

        return [
            "start_time" => time(),
            "last_time" => time(),
            "end_time" => 0,
            "status" => "empty",
            "sync_posts_finished" => 0,
            "sync_posts_failed" => 0,
            "sync_posts_total" => wp_count_posts()->publish,
            "sync_posts_created" => 0,
            "sync_posts_updated" => 0,
        ];
    }

    /**
     * @param array $array
     * @return array
     */
    public function set_task(array $array = []) {
        $this->task = array_merge($this->get_default_task(), $array);

        $array = (array) $this->task;

        update_option(self::TASK_SLUG, (string) serialize($array), false);

        return (array) $array;
    }

    /**
     * @param array $array
     * @param bool $force
     * @return array
     */
    public function update_task(array $array = [], $force = false) {

        if($force)
        {
            $array = array_merge($this->fresh_task(), $array);
        }
        else
        {
            $array = array_merge($this->get_task(), $array);
        }

        $this->task =  $array;

        update_option(self::TASK_SLUG, (string) serialize($array), false);

        return $array;
    }

    /**
     * Settings to setup javascript file and integration library
     *
     * @return array|mixed|void
     */
    public function get_integration_settings() {
        try {
            $mainSettings =
                [
                    'lang'          => $this->get_site_lang(),
                    'public_key'    => $this->get_option('wk_r_key', NULL),
                    'mode'          => $this->get_option('wk_server', 'prod'),
                    'version'       => $this->get_option('wk_api_version', 'v1'),
                    'analytics'     => [
                        'parseUTM'      => (bool) $this->get_option('wk_analytics', false),
                    ],
                    'call'          => [
                        'use'           => (bool) $this->get_option('wk_calls_use', true),
                        'debug'         => (bool) $this->get_option('wk_calls_debug', false),
                        'classForHandleClick' => $this->get_option('wk_calls_handle_click', 'wk-call'),
                        'classThatReactOnTheUsersStatus' => $this->get_option('wk_calls_users_status', 'wk-call-status-user'),
                        'classThatReactOnTheUsersPlans'  => $this->get_option('wk_calls_users_plans', 'wk-call-status-plans'),
                        'classThatReactOnTheUsersEvents' => $this->get_option('wk_calls_users_events', 'wk-call-status-events'),
                    ]
                ];

            $additionalOptions = json_decode(base64_decode($this->get_option('wk_additional_options', null)), true) ?: [];
            // Set default Sign In settings and html wrapper, if not provide in settings.
            if( !isset($additionalOptions['auth']['modal']) ) {
                $additionalOptions['auth']['modal'] = [
                    "content" => $this->get_default_sign_in_template()
                ];
            }

            if( !isset($additionalOptions['auth']['firebase']['elementSelector']) ) {
                $additionalOptions['auth']['firebase']['elementSelector'] = "#wk-fb-auth-wrapper";
            }

            if((bool) $this->get_option('wk_auth_migrated_users', false) === true) {
                $additionalOptions['auth']['firebase']['genuineForm']           = false;
                $additionalOptions['auth']['firebase']['genuinePasswordReset']  = false;
                $additionalOptions['auth']['firebase']['authOnPasswordReset']   = true;
            }

            $auth_with_empty_pass = (bool) $this->get_option('wk_auth_allow_empty_pass', false);
            if($auth_with_empty_pass === true) {
                $additionalOptions['auth']['firebase']['passwordSignInIgnoreValidation']   = $auth_with_empty_pass;
            }


            $settings['titles'] = [];
            // Set site elements titles
            if( isset($additionalOptions['titles']) && !empty($additionalOptions['titles']) ) {
                $settings['titles'] = $additionalOptions['titles'];
            }

            $settings['integration'] = array_merge(
                $mainSettings,
                $additionalOptions
            );

            //Selected post types for paywalled
            $selectedPostTypes = [];
            $registeredPostTypes = $this->get_option("wk_check_post_type_access");
            if( !empty($registeredPostTypes) && $this->get_option("wk_check_post_access") )
            {
                $selectedPostTypes = array_keys( array_filter($registeredPostTypes, function($post_type) { return $post_type; }) );
            }

            $settings['config'] = [
                'sign_in_button'            => (bool) $this->get_option('wk_sign_in_button', true),
                'debug'                     => (bool) $this->get_option('wk_debug', false),
                'check_post_types'          => (array) $selectedPostTypes,
                'reload_on_logout'          => (bool) $this->get_option('wk_reload_on_logout', true),
                'wk_free_paragraph'         => (int) $this->get_option('wk_free_paragraph', 1),
                'wk_paywall_display_type'   => (int) $this->get_option('wk_paywall_display_type', 0),
                'content_class_selector'    => $this->get_content_class_selector(),
                'custom_content_selector'   => $this->get_custom_content_selector(),
                'paywall'                   => [
                    'content'               => base64_decode($this->get_option("wk_content_access_html")),
                ],
                'wk_auth_migrated_users'    => (bool) $this->get_option('wk_auth_migrated_users', false),
                'wk_content_key_prefix'     => $this->get_option('wk_content_key_prefix', ''),
                'wk_auth_allow_empty_pass'  => (bool) $this->get_option('wk_auth_allow_empty_pass', false),
                'wk_auth_migrated_users_text' => base64_decode($this->get_option('wk_auth_migrated_users_text', '')),
                'skip_lorem'                => false,
                'parse_scripts'             => true,
                'inline_modals_selector'    => $this->get_inline_modals_selector(),
                'wk_modal_after_sign_in'    => apply_filters( 'wallkit_override_inline_modal_after_sign_in', 'account-settings'),
                'wk_my_account_page_url'    => $this->get_option('wk_my_account_page_url', ''),
            ];

            $settings = apply_filters( 'wallkit_override_integration_settings', $settings);

            return $settings;
        }
        catch (\Exception $exception)
        {
            return [];
        }
    }

    /**
     * Default Sign In form html wrapper
     *
     * @return string
     */
    public function get_default_sign_in_template() {
        $img = '';
        $logo = $this->resource_settings->get_logo();

        if(!empty($logo)) {
            $img = "<img style='margin: 0 auto; padding-bottom: 20px;' src='{$logo}' width='220' height='85' alt='Wallkit'>";
        }

        $sign_in_html = "<div class='wk-popup-auth-container'>
                    <div>
                        <div class='wk-auth-header' style='text-align: center;'>
                            {$img}
                        </div>
                        <div class='wk-auth-content'>
                            <div class='wk-auth-content-center'>
                                <div id='wk-fb-auth-wrapper'></div>
                            </div>
                        </div>
                    </div>
                </div>";

        return apply_filters( 'wallkit_customize_sign_in_html', $sign_in_html);
    }

    /**
     * Setup translations for setup script
     *
     * @return array
     */
    public function get_script_translations() {
        return [
            'sign_in'       => __('Sign&nbsp;in', 'wallkit'),
            'my_account'    => __('My&nbsp;Account', 'wallkit'),
        ];
    }

    /**
     * @return array
     */
    public function get_public_settings() {
        try {
            $settings =
                [
                    'public_key' => $this->get_option('wk_r_key', NULL),
                    'api_version' => $this->get_option('wk_api_version', 'v1'),
                    'plugin_url' => WPWKP_plugin_url(),
                    'plugin_version' => WPWKP_VERSION,
                ];

            switch($this->get_option("wk_server", "prod")) {
                case "prod" :
                    $settings["api_host"] = 'api-s2.wallkit.net';
                    $settings["auth_url"] = 'https://wallkit.net/popups';
                    break;
                case "dev" :
                    $settings["api_host"] = 'api.dev.wallkit.net';
                    $settings["auth_url"] = 'https://dev.wallkit.net/popups';
                    break;
                default :
                    throw new Wallkit_Wp_SDK_Exception("Unknown server configuration");
                    break;
            }

            return $settings;
        }
        catch (\Exception $exception)
        {
            return [];
        }
    }

    /**
     * @return array
     */
    public function get_settings_connection() {
        $settings = (array) $this->get_public_settings();
        $settings["secret_key"] =  $this->get_option('wk_rs_key', NULL);
        return $settings;
    }

    /**
     *  ENABLE CACHE IF DISABLED
     */
    public function disable_cache() {
        if(!defined('ENABLE_CACHE')) {
            define('ENABLE_CACHE', false);
        }
    }

    /**
     * Setup wallkit SDK
     */
    private function setup_wallkit_sdk() {
        try {
            $this->wallkitSDK = new WallkitSDK\WallkitSDK(
                $this->get_settings_connection()
            );
        }
        catch (\Exception $exception)
        {
            $this->wallkitSDK = null;
        }
    }

    /**
     * Setup resource settings
     */
    private function setup_resource_settings() {
        try {
            $this->resource_settings = new Wallkit_Wp_Resource_Settings($this->wallkitSDK);
        }
        catch (\Exception $exception)
        {
        }
    }

    /**
     * Get Wallkit SDK
     *
     * @return \WallkitSDK\WallkitSDK
     */
    public function get_sdk() {
        return $this->wallkitSDK;
    }


    public function get_resource_settings() {
        return $this->resource_settings;
    }

    public function get_content_class_selector() {
        if((int) $this->get_option('wk_paywall_display_type') === 1 && empty($this->get_custom_content_selector()) ) {
            $contentClassSelector = $this->get_option('wk_content_class_selector', 'wkwp-post-content');

            if(empty($contentClassSelector)) {
                $contentClassSelector = 'wkwp-post-content';
            }

            return $contentClassSelector;
        }

        return '';
    }

    public function get_custom_content_selector() {
        if((int) $this->get_option('wk_paywall_display_type') === 1) {
            $customContentSelector = $this->get_option('wk_custom_content_selector', '');

            return $customContentSelector;
        }

        return '';
    }

    public function get_inline_modals_selector() {
        $inlineModalsSelector = $this->get_option('wk_modals_inline_selector', '#wk-inline-popup-modal');

        return $inlineModalsSelector;
    }

}
