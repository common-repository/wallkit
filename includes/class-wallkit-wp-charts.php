<?php
/**
 * @package    Wallkit_Wp
 * @subpackage Wallkit_Wp/admin
 * @author     Wallkit <dev@wallkit.net>
 */
class Wallkit_Wp_Charts {

    /**
     * @var \WallkitSDK\WallkitSDK
     */
    private $wallkitSDK;

    /**
     * @var Wallkit_Wp_Admin
     */
    private $wallkit_Wp_Settings;

    /**
     * @var Wallkit_Wp_Collection
     */
    private $collection;

    /**
     * Wallkit_Wp_Charts constructor.
     *
     * @param Wallkit_Wp_Collection $wallkit_Wp_Collection
     */
    public function __construct(Wallkit_Wp_Collection $wallkit_Wp_Collection) {

        $this->wallkitSDK = $wallkit_Wp_Collection->get_settings()->get_sdk();
        $this->wallkit_Wp_Settings = $wallkit_Wp_Collection->get_settings();
        $this->collection = $wallkit_Wp_Collection;
    }

    /**
     * @return array
     */
    public function get_activity() {
        if(!$this->wallkitSDK instanceof \WallkitSDK\WallkitSDK)
        {
            return [];
        }
        try {
            $result = $this->wallkitSDK->get('/admin/charts/activity', [
                "groupby" => "days",
                "period" => "1 month"
            ], true)->toArray();

        }
        catch (\Exception $exception) {
            return [
                "error" => true,
                "message" => $exception->getMessage()
            ];
        }

        return $result["data"];
    }
}
