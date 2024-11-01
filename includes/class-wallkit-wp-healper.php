<?php
/**
 * Wallkit helpers.
 *
 * @since      1.1.17
 * @package    Wallkit_Wp
 * @subpackage Wallkit_Wp/includes
 * @author     Wallkit <dev@wallkit.net>
 */

class Wallkit_Wp_Helper {

    /**
     * @var Wallkit_Wp_Collection
     */
    private $wallkit_Wp_Collection;

    /**
     * @var \WallkitSDK\WallkitSDK
     */
    private $wallkitSDK;

    /**
     * Wallkit_Wp_Templates constructor.
     *
     * @param Wallkit_Wp_Collection $wallkit_Wp_Collection
     */
    public function __construct(Wallkit_Wp_Collection $wallkit_Wp_Collection) {

        $this->wallkit_Wp_Collection = $wallkit_Wp_Collection;

        $this->wallkitSDK = $this->wallkit_Wp_Collection->get_settings()->get_sdk();
    }

    /**
     * Helper get list popups for modals
     * @return array
     */
    public function get_popups() {
        $template_list = [];
        try {

            if($this->wallkitSDK instanceof \WallkitSDK\WallkitSDK) {
                $response = $this->wallkitSDK->get('/admin/popup-templates', [
                    "filter" => [
                        "status" => "published",
                        "active" => true
                    ]
                ], true)->toArray();

                $response["items"] = (array) (isset($response["items"]) ? $response["items"] : []);

                if(count($response["items"])) {
                    foreach($response["items"] AS $item) {
                        if(!isset($item["slug"]))
                            continue;
                        $template_list[$item["slug"]] = $item["title"];
                    }
                }
            }

        }
        catch (\Exception $exception)
        {

        }

        return $template_list;
    }

}
