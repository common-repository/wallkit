<?php
/**
 * Fired during plugin deactivation.
 * @since      1.1.17
 * @package    Wallkit_Wp
 * @subpackage Wallkit_Wp/includes
 * @author     Wallkit <dev@wallkit.net>
 */
class Wallkit_Wp_Deactivator {

	public static function deactivate() {

	    $Wallkit_Wp = new Wallkit_Wp();

	    if($Wallkit_Wp instanceof Wallkit_Wp &&
            $Wallkit_Wp->get_collection() instanceof Wallkit_Wp_Collection)
        {
            if($Wallkit_Wp->get_collection()
                ->get_settings() instanceof Wallkit_Wp_Settings)
            {
                $Wallkit_Wp->get_collection()
                    ->get_settings()
                    ->update_option("wk_is_active", false);
            }

        }

	}

}
