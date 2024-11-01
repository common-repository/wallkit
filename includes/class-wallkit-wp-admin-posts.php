<?php

/**
 * @package    Wallkit_Wp
 * @subpackage Wallkit_Wp/admin
 * @author     Wallkit <dev@wallkit.net>
 */
class Wallkit_Wp_Admin_Posts {

    /**
     * @var \WallkitSDK\WallkitSDK
     */
    private $wallkitSDK;

    /**
     * @var Wallkit_Wp_Admin
     */
    private $wallkit_Wp_Settings;

	/**
	 * Content Key prefix
	 */
    private $content_key_prefix='';

    /**
     * Wallkit_Wp_Admin_Posts constructor.
     *
     * @param Wallkit_Wp_Admin $wallkit_Wp_Admin
     */
    public function __construct(Wallkit_Wp_Collection $wallkit_Wp_Collection) {

        $this->wallkitSDK = $wallkit_Wp_Collection->get_settings()->get_sdk();
        $this->wallkit_Wp_Settings = $wallkit_Wp_Collection->get_settings();
        $this->setContentKeyPrefix();
    }

	/**
	 * Set Content Key Prefix
	 */
    private function setContentKeyPrefix() {
    	if (is_multisite()) {
    		global $wpdb;
    		$this->content_key_prefix = $wpdb->prefix;
	    }

        if( !empty($this->wallkit_Wp_Settings->get_option("wk_content_key_prefix")) ) {
            $this->content_key_prefix = $this->wallkit_Wp_Settings->get_option("wk_content_key_prefix") . '_' . $this->content_key_prefix;
        }
    }

    /**
     * @return bool
     */
    private function isActive() {
        return (bool) false !== ($this->wallkitSDK instanceof \WallkitSDK\WallkitSDK);
    }

    /**
     * @param WP_Post $post
     * @return array
     */
    private function getImagesFromPost( WP_Post $post) {
        $images = [];
        $image_url = wp_get_attachment_url(get_post_thumbnail_id($post->ID));
        if($image_url)
        {
            $image_url = trim(rawurldecode($image_url));
            if (filter_var($image_url, FILTER_VALIDATE_URL))
            {
                array_push($images, [
                    "url" => $image_url,
                ]);
            }
        }
        $catch_images = $this->catchImagesFromPost($post);
        if($catch_images)
        {
            foreach($catch_images AS $image)
            {
                $image = trim(rawurldecode($image));
                if (filter_var($image, FILTER_VALIDATE_URL))
                {
                    array_push($images, [
                        "url" => $image,
                    ]);
                }
            }
        }

        return $images;
    }

    /**
     * @param $title
     * @return bool|string
     */
    private function filterTitle($title) {

        $title = trim(rawurldecode($title));

        if(strlen($title) > 127)
        {
            return substr($title, 0,127);
        }
        return $title;
    }

    /**
     * @param $post_ID
     * @param WP_Post $post
     * @return \WallkitSDK\WallkitResponse
     * @throws Wallkit_Wp_Content_Exception
     * @throws \WallkitSDK\Exceptions\WallkitApiException
     * @throws \WallkitSDK\Exceptions\WallkitException
     */
    public function updatedPost($post_ID, WP_Post $post) {
        $data = [
            'key' => $this->content_key_prefix . $post_ID,
            'title' => $this->filterTitle($post->post_title),
            'description' => $post->post_content,
            'content_type' => trim($post->post_type),
            'published_at' => $post->post_date_gmt,
            'price' => 0,
            'currency' => 'USD',
            'link' => get_permalink($post_ID),
            "images" => $this->getImagesFromPost($post),
            "taxonomies" => $this->getTaxonomiesFromPost($post),
            "extra" => []
        ];

        $result = $this->wallkitSDK->put('/admin/content/' . $this->content_key_prefix . $post_ID, $data, true);

        if(!$result) {
            throw new \Wallkit_Wp_Content_Exception("Update post is failed");
        }

        return $result;
    }

    /**
     * @param WP_Post $post
     * @return array
     */
    private function getCategoryByPost(WP_Post $post) {
        $terms = [];
        foreach(wp_get_post_categories($post->ID) AS $category) {

            $category = get_term( $category, 'category' );

            if($category) {
                array_push($terms, [
                    "key" => $category->slug,
                    "title" => trim(rawurldecode($category->name))
                ]);
            }
        }
        return $terms;
    }

    /**
     * @param WP_Post $post
     * @return array
     */
    private function getTaxonomiesFromPost(WP_Post $post)
    {
        $availableTax = $this->wallkit_Wp_Settings->getOptionSyncTaxonomies();
	    $taxonomies = $tax_w_labels = $tax_names = [];
	    $tax = get_object_taxonomies($post,'object');

	    if (is_countable($tax)) {
		    foreach ($tax as $tax_item) {
		        if(!$availableTax[$tax_item->name]) continue;

			    $tax_w_labels[$tax_item->name] = [
				    'key' => $tax_item->name,
				    'title' => $tax_item->label,
				    'terms' => []
			    ];
			    $tax_names[]= $tax_item->name;
		    }
	    }

	    $terms = wp_get_post_terms($post->ID, $tax_names);

	    if (is_countable($terms)) {
		    foreach ($terms as $term_item) {
			    $tax_w_labels[$term_item->taxonomy]['terms'][] = [
				    "key" => $term_item->slug,
				    "title" => $term_item->name,
			    ];
		    }
	    }
	    if (is_countable($tax_w_labels)) {
		    $tax_w_labels = array_filter($tax_w_labels, function($item) {
			    return count($item['terms']);
		    });
	    }
	    foreach ($tax_w_labels as $tax_w_l_item) {
		    $taxonomies[] = $tax_w_l_item;
	    }

        return apply_filters('wallkit_override_post_taxonomies_sync', $taxonomies, $post);
    }

    /**
     * @param $post_ID
     * @param WP_Post $post
     * @return \WallkitSDK\WallkitResponse
     * @throws Exception
     */
    public function createPost($post_ID, WP_Post $post) {
        $data = [
            'key' => $this->content_key_prefix . $post_ID,
            'title' => $this->filterTitle($post->post_title),
            'description' => $post->post_content,
            'content_type' => trim($post->post_type),
            'published_at' => $post->post_date_gmt,
            'price' => 0,
            'currency' => 'USD',
            'link' => get_permalink($post_ID),
            "images" => $this->getImagesFromPost($post),
            "taxonomies" => $this->getTaxonomiesFromPost($post),
            "extra" => []
        ];

        $result = $this->wallkitSDK->post('/admin/content', $data, true);

        if(!$result) {
            throw new Exception("Create post in wallkit is faild");
        }

        return $result;
    }

    /**
     * @param WP_Post $post
     * @return mixed
     */
    public function catchImagesFromPost(WP_Post $post) {
        ob_start();
        ob_end_clean();
        preg_match_all('/<img[\s]+[^>]*src=[\'"]([^\'"]+)[\'"][^<]+>/i', $post->post_content, $matches);

        if(isset($matches[1])) {
            return $matches[1];
        }
        return [];
    }

    /**
     * @param $post_ID
     */
    public function deletePost($post_ID) {
        try {
            $result = $this->wallkitSDK->delete('/admin/content/' . $this->content_key_prefix . $post_ID, [], true);

            if(!$result) {
                throw new Exception("Delete post in wallkit is failed");
            }

        }
        catch (\Exception $exception)
        {
            //Deprecated log statement
        }
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return array
     */
    private function getPartOfPosts($limit = 100, $offset = 0) {

        $Posts = get_posts([
            "posts_per_page" => $limit,
            "offset" => $offset,
            'suppress_filters' => false
        ]);

        return $Posts;
    }

    /**
     * @throws Exception
     */
    public function run_sync_posts() {

        set_time_limit(0);
        ignore_user_abort(true);

        if(!$this->isActive())
        {
            $this->wallkit_Wp_Settings->update_task([
                "status" => "disabled",
                "last_time" => time()
            ]);

            return;
        }

        $i = $sync_posts_finished = $sync_posts_created = $sync_posts_updated = $sync_posts_failed = 0;

        $total = (int) wp_count_posts()->publish;

        $task = $this->wallkit_Wp_Settings->get_task(true);

        if(in_array($task["status"], ["queued", "continue"], true))
        {
            $i = (int) ceil($task["sync_posts_finished"] / 100) - 1;

            if( $i  < 0 ) {
                $i = 0;
            }
            else
            {
                $sync_posts_finished = $task["sync_posts_finished"];
                $sync_posts_created = $task["sync_posts_created"];
                $sync_posts_updated = $task["sync_posts_updated"];
                $sync_posts_failed = $task["sync_posts_failed"];
            }

            $this->wallkit_Wp_Settings->update_task([
                "status" => "running",
                "sync_posts_finished" => $sync_posts_finished,
                "sync_posts_created" => $sync_posts_created,
                "sync_posts_updated" => $sync_posts_updated,
                "sync_posts_failed" => $sync_posts_failed,
                "sync_posts_total" => $total,
                "last_time" => time()
            ]);

            do {

                $task = $this->wallkit_Wp_Settings->fresh_task();

                if(in_array($task["status"],["broken", "stop", "fail", "stopped"],true))
                {
                    $this->wallkit_Wp_Settings->update_task([
                        "status" => "stopped",
                        "last_time" => time()
                    ]);

                    return;
                }

                if($task["status"] === "pause") {
                    $this->wallkit_Wp_Settings->update_task([
                        "status" => "paused",
                        "last_time" => time()
                    ]);
                    continue;
                }

                if($task["status"] === "paused") {
                    sleep(5);
                    continue;
                }

                $Posts = $this->getPartOfPosts(100, $i*100);

                foreach($Posts AS $k =>  $post) {

                    if(($k % 10) === 0)
                    {
                        $task = $this->wallkit_Wp_Settings->fresh_task();
                        if(in_array($task["status"], [
                            "stopped",
                            "stop",
                            "pause",
                            "paused",
                        ],true))
                        {
                            $i--;
                            break;
                        }
                    }

                    if((($sync_posts_finished / $total) * 100) > 10 && (($sync_posts_failed / $sync_posts_finished) * 100) >= 95)
                    {
                        $this->wallkit_Wp_Settings->update_task([
                            "status" => "broken",
                            "last_time" => time()
                        ]);
                        return;
                    }

                    try {
                        $result = $this->updatedPost($post->ID, $post);

                        if($result->getCode() === 200)
                        {
                            $sync_posts_finished++;
                            $sync_posts_updated++;
                        }
                        elseif($result->getCode() === 404)
                        {
                            $result = $this->createPost($post->ID, $post);

                            if($result->getCode() === 201)
                            {
                                $sync_posts_finished++;
                                $sync_posts_created++;
                            }
                        }

                    }
                    catch (\WallkitSDK\Exceptions\WallkitApiException $exception)
                    {

                        if(preg_match('/not found/i', $exception->getMessage()))
                        {
                            $result = $this->createPost($post->ID, $post);

                            if($result->getCode() === 201)
                            {
                                $sync_posts_finished++;
                                $sync_posts_created++;
                            }
                        }
                        else
                        {
                            $sync_posts_failed++;
                            $sync_posts_finished++;

                            $this->wallkit_Wp_Settings->update_task([
                                "status" => "fail",
                                "last_time" => time()
                            ]);
                            //return;
                        }

                    }
                    catch (\Exception $exception)
                    {
                        $sync_posts_failed++;
                        $sync_posts_finished++;

                        $this->wallkit_Wp_Settings->update_task([
                            "status" => "fail",
                            "last_time" => time()
                        ]);

                        //return;
                    }


                    if(($k % 20) === 0 || count($Posts)<100) {

                        /**
                         * UPDATE COUNT ready posts
                         */
                        $this->wallkit_Wp_Settings->update_task([
                            "status" => "running",
                            "sync_posts_finished" => $sync_posts_finished,
                            "sync_posts_created" => $sync_posts_created,
                            "sync_posts_updated" => $sync_posts_updated,
                            "sync_posts_failed" => $sync_posts_failed,
                            "last_time" => time()
                        ]);
                    }
                }

                $i++;

            } while($total > $sync_posts_finished || $i < ($total /100) + 10 );

            /**
             * UPDATE COUNT ready posts
             */
            $this->wallkit_Wp_Settings->update_task([
                "sync_posts_finished" => $sync_posts_finished,
                "sync_posts_created" => $sync_posts_created,
                "sync_posts_updated" => $sync_posts_updated,
                "sync_posts_failed" => $sync_posts_failed,
                "last_time" => time(),
                "end_time" => time(),
                "status" => "finished",
            ]);

        }
        elseif(in_array($task["status"], [
            "broken",
            "stop",
            "fail",
            "stopped",
            "disabled",
            "unknown",
            "running",
        ], true))
        {
            return ;
        }
        else
        {
            return;
        }
    }

    /**
     * @param WP_Post $WP_Post
     * @return array
     */
    public function get_post(WP_Post $WP_Post)
    {
        try {
            if(!isset($WP_Post->ID))
            {
                throw new Wallkit_Wp_Content_Exception("Incorrect post id for export content");
            }

            $result = $this->wallkitSDK->get('/admin/content/' . $this->content_key_prefix . $WP_Post->ID, [], true);

            return $result->toArray();
        }
        catch (\Exception $exception)
        {
            return [
                "error" => true,
                "message" => $exception->getMessage()
            ];
        }

    }

    /**
     * @param WP_Post $WP_Post
     * @return array
     */
    public function get_post_statistic(WP_Post $WP_Post)
    {
        try {
            if(!isset($WP_Post->ID))
            {
                throw new Wallkit_Wp_Content_Exception("Incorrect post id for export content");
            }

            $result = $this->wallkitSDK->get('/admin/content/' . $this->content_key_prefix . $WP_Post->ID . '/statistic', [], true);

            return $result->toArray();
        }
        catch (\Exception $exception)
        {
            return [
                "error" => true,
                "message" => $exception->getMessage()
            ];
        }
    }


}
