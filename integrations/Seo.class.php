<?php
namespace YoudsFramework\Integrations;
use YoudsFramework\Request\ParameterHolder;

// +----------------------------------------------------------------------------+
// | This file is part of the Youds Framework package.                         	|
// | Copyright (c) Youds Media Limited; see https://framework.youds.com			|
// |                                                                           	|
// | For the full copyright and license information, please view the LICENSE 	|
// | file that was distributed with this source code. 							|
// +----------------------------------------------------------------------------+

/**
 * IntegrationsSeo provides API access to SEO data 
 * from DataForSEO
 *
 * @package    Youds Framework - https://framework.youds.com
 * @subpackage generator
 *
 * @author     Craig Fairhurst <craig.fairhurst@youds.com>
 *
 * @since      0.1
 *
 * @version    $Id$
 */
class Seo extends ParameterHolder
{
	/**
	 * Retrieve DataforSeo response
	 *
	 * @return void
	 * @author Craig Fairhurst <craig.fairhurst@youds.com>
	 */
	public function seo ($action, $config = array())
	{
		// first include our library
		require_once(Config::get('core.src_dir') . '/integrations/DataForSEO/RestClient.php');

		// next define our environment 
		if (Config::get('core.development') == 'development')
			$api_url = 'https://api.dataforseo.com/';
		else
			$api_url = 'https://api.dataforseo.com/';			

		$client = new RestClient(
			$api_url, null, Config::get('seo.login'), Config::get('seo.password')
		);
		$post_array = array();
		
		// default values
		if (!isset($config['language']))
			$config['language'] = 'English';
		if (!isset($config['location']))
			$config['location'] = 'United Kingdom';
		if (!isset($config['searchEngine']))
			$config['searchEngine'] = 'Google';
		if (!isset($config['platform']))
			$config['platform'] = 'Facebook';
		if (!isset($config['limit']))
			$config['limit'] = 10;
		if (!isset($config['keywords']) && isset($config['keyword']))
			$config['keywords'] = $config['keyword'];
		if (isset($config['keywords'])):
			$_keywords = (strstr($config['keywords'], ',')?explode(',', $config['keywords']):array($config['keywords']));
			array_walk($_keywords, function($value, $key) {
				global $_keywords;
				$_keywords[$key] = trim($value);
			});
			global $_keywords;
			$config['keywords'] = $_keywords;
		endif;
		if (isset($config['url'])):
			$_url = (strstr($config['url'], ',')?explode(',', $config['url']):array($config['url']));
			array_walk($_url, function($value, $key) {
				global $_url;
				$_url[$key] = str_replace('http://', '', str_replace('https://', '', str_replace('www.', '', trim($value))));
			});
			global $_url;
			$config['url'] = $_url;
		endif;
		if (isset($config['categories'])):
			$_categories = (strstr($config['categories'], ',')?explode(',', $config['categories']):array($config['categories']));
			array_walk($_categories, function($value, $key) {
				global $_categories;
				$_categories[$key] = str_replace('http://', '', str_replace('https://', '', str_replace('www.', '', trim($value))));
			});
			global $_categories;
			$config['categories'] = $_categories;
		endif;
		
		$actions = array(
			'google_play_store' => ['title', 'description', 'order_by', 'rating', 'limit'],
			'keyword_difficulty' => ['keywords', 'language', 'location', 'searchEngine'],
			'search_type' => ['keywords', 'language', 'location'],
			'search_volume' => ['keywords', 'language', 'location'],
			'search_volume_history' => ['keywords', 'language', 'location'],
			'search_volume_from_url' => ['url', 'language', 'location', 'searchEngine'],
			'search_volume_from_url_history' => ['url', 'language', 'location', 'searchEngine'],
			'social_media_url_search' => ['url', 'platform'],
			'categories_from_url' =>  ['url', 'language', 'location', 'limit'],
			'competitors_from_url' => ['url', 'competitors', 'location', 'language', 'limit', 'searchEngine'],
			'domain_compare' => ['url1', 'url2', 'location', 'language', 'limit'],
			'category_metrics_from_url' => ['categories', 'location', 'language', 'limit'],
			'rank_from_url' => ['url', 'location', 'language', 'limit'],
			'rank_from_url_history' => ['url', 'location', 'language', 'limit'],
			'serp' => ['keywords', 'location', 'language', 'limit'],
			'serp_history' => ['keywords', 'location', 'language', 'limit'],
			'serp_competitors' => ['keywords', 'location', 'language', 'limit'],
			'keyword_suggestions' => ['keywords', 'location', 'language', 'limit'],
			'keyword_suggestions_from_url' => ['url', 'location', 'language', 'limit'],
			'related_keywords' => ['keywords', 'location', 'language', 'limit'],
			'relevant_pages' => ['url', 'location', 'language', 'limit'],
			'domain_analytics' => ['url', 'location', 'language', 'limit'],
			'domain_analytics_from_keyword' => ['keywords', 'location', 'language', 'limit'],
			'google_ads' => ['keywords', 'location', 'language', 'limit'],
			'google_ads_from_url' => ['url', 'location', 'language', 'limit']
		);
		echo "<pre>";
		foreach ($actions as $key => $entry):
			$a = 1;
			echo $key . ': ';
			foreach ($entry as $value):
				if (count($entry) > $a)
					echo $value . ', ';
				else
					echo $value;
				$a++;
			endforeach;
			echo PHP_EOL;
		endforeach;
		echo "</pre>";exit;
		switch ($action):
			
			case 'keyword_difficulty':
				$keys = ['keywords', 'language', 'location', 'searchEngine'];
				$post_array[] = array(
					'keywords' => $config['keywords'],
					'language_name' => $config['language'],
					'location_name' => $config['location']
				);
				$method = 'dataforseo_labs/' . strtolower($config['searchEngine']) . '/bulk_keyword_difficulty/live';
				break;
			case 'search_type':
				$keys = ['keywords', 'language', 'location'];
				$post_array[] = array(
					'keywords' => $config['keywords'],
					'language_name' => $config['language'],
					'location_name' => $config['location']
				);
				$method = 'dataforseo_labs/google/search_intent/live';
				break;
			case 'search_volume':
				$keys = ['keywords', 'language', 'location'];
				$post_array[] = array(
					'keywords' => $config['keywords'],
					'language_name' => $config['language'],
					'location_name' => $config['location']
				);
				$method = 'dataforseo_labs/google/historical_search_volume/live';
				break;
			case 'search_volume_history':
				$keys = ['keywords', 'language', 'location'];
				$post_array[] = array(
					'keywords' => $config['keywords'],
					'language_name' => $config['language'],
					'location_name' => $config['location']
				);
				$method = 'dataforseo_labs/google/historical_search_volume/live';
				break;
			case 'search_volume_from_url':
				$keys = ['url', 'language', 'location', 'searchEngine'];
				$post_array[] = array(
					'targets' => $config['url'],
					'language_name' => $config['language'],
					'location_name' => $config['location'],
					'item_types' => [
						'organic',
						'paid'
					]
				);
				$method = 'dataforseo_labs/' . strtolower($config['searchEngine']) . '/bulk_traffic_estimation/live';
				break;
			case 'search_volume_from_url_history':
				$keys = ['url', 'language', 'location', 'searchEngine'];
				$post_array[] = array(
					'targets' => $config['url'],
					'language_name' => $config['language'],
					'location_name' => $config['location'],
					'item_types' => [
						'organic',
						'paid'
					]
				);
				$method = 'dataforseo_labs/' . strtolower($config['searchEngine']) . '/bulk_traffic_estimation/live';
				break;
			case 'social_media_url_search':
				$keys = ['url', 'platform'];
				$post_array[] = array(
					'targets' => $config['url']
					
				);
				$method = 'business_data/social_media/' . strtolower($config['platform']) . '/live';
				break;
				
			case 'categories_from_url':
				$keys = ['url', 'language', 'location', 'limit'];
				$post_array[] = array(
					'target' => $config['url'][0],
					'language_name' => $config['language'],
					'location_name' => $config['location'],
					'filters' => ['metrics.organic.count', '>=', 25],
					'limit' => $config['limit']
				);
				$method = 'dataforseo_labs/google/categories_for_domain/live';
				break;
			case 'competitors_from_url':
				$keys = ['url', 'competitors', 'location', 'language', 'limit', 'searchEngine'];
				$post_array[] = array(
					'target' => $config['url'][0],
					'language_name' => $config['language'],
					'location_name' => $config['location'],
					'intersecting_domains' => $config['competitors'],
					'filters' => [
						['metrics.organic.count', '>=', 25]
					],
					'limit' => $config['limit'],
					'exclude_top_domains' => true
				);
				$method = 'dataforseo_labs/' . $config['searchEngine'] . '/competitors_domain/live';
				break;
			case 'domain_compare':
				$keys = ['url1', 'url2', 'location', 'language', 'limit'];
				$post_array[] = array(
					'target1' => $config['url'][0],
					'target2' => $config['url'][1],
					'language_name' => $config['language'],
					'location_name' => $config['location'],
					'filters' => [
						['first_domain_serp_element.etv', '>=', 0]
					],
					'limit' => $config['limit'],
					'exclude_top_domains' => true
				);
				$method = 'dataforseo_labs/google/domain_intersection/live';
				break;
			
			case 'category_metrics_from_url':
				$keys = ['categories', 'location', 'language', 'limit'];
				$post_array[] = array(
					'category_codes' => $config['categories'],
					'language_name' => $config['language'],
					'location_name' => $config['location'],
					'first_date' => date('Y-m-d', strtotime('-6 months')),
					'second_date' => date('Y-m-d', strtotime('-1 week')),
					'filters' => [
						['organic_etv', '>', 1000]
					],
					'limit' => $config['limit']
				);
				$method = 'dataforseo_labs/google/domain_metrics_by_categories/live';
				break;
			case 'rank_from_url':
				$keys = ['url', 'location', 'language', 'limit'];
				$post_array[] = array(
					'target' => $config['url'][0],
					'language_name' => $config['language'],
					'location_name' => $config['location'],
					'limit' => $config['limit']
				);
				$method = 'dataforseo_labs/google/domain_rank_overview/live';
				break;
			case 'rank_from_url_history':
				$keys = ['url', 'location', 'language', 'limit'];
				$post_array[] = array(
					'target' => $config['url'][0],
					'language_name' => $config['language'],
					'location_name' => $config['location'],
					'limit' => $config['limit']
				);
				$method = 'dataforseo_labs/google/historical_rank_overview/live';
				break;
			
			case 'serp':
				$keys = ['keywords', 'location', 'language', 'limit'];
				$post_array[] = array(
					'keyword' => $config['keywords'][0],
					'language_name' => $config['language'],
					'location_name' => $config['location'],
					'limit' => $config['limit']
				);
				$method = 'serp/google/organic/live/advanced';
				break;
	
			case 'serp_history':
				$keys = ['keywords', 'location', 'language', 'limit'];
				$post_array[] = array(
					'keyword' => $config['keywords'][0],
					'language_name' => $config['language'],
					'location_name' => $config['location'],
					'date_from' => date('Y-m-d', strtotime('-6 months')),
					'date_to' => date('Y-m-d', strtotime('-1 week')),
					'limit' => $config['limit']
				);
				$method = 'dataforseo_labs/google/historical_serps/live';
				break;
			case 'serp_competitors':
				$keys = ['keywords', 'location', 'language', 'limit'];
				$post_array[] = array(
					'keywords' => $config['keywords'],
					'language_name' => $config['language'],
					'location_name' => $config['location'],
					'filters' => [
						['relevant_serp_items', '>', 0],
						'or',
						['median_position', 'in', [ 1, 10 ]]
					],
					'limit' => $config['limit']
				);
				$method = 'dataforseo_labs/google/serp_competitors/live';
				break;		
			case 'keyword_suggestions':
				$keys = ['keywords', 'location', 'language', 'limit'];
				$post_array[] = array(
					'keywords' => $config['keywords'],
					'language_name' => $config['language'],
					'location_name' => $config['location'],
					'filters' => [
						['keyword_info.search_volume', '>', 10]
					],
					'limit' => $config['limit']
				);
				$method = 'dataforseo_labs/google/keyword_ideas/live';
				break;
			
			case 'keyword_suggestions_from_url':
				$keys = ['url', 'location', 'language', 'limit'];
				$post_array[] = array(
					'target' => $config['url'][0],
					'language_name' => $config['language'],
					'location_name' => $config['location'],
					'filters' => [
						['keyword_data.keyword_info.search_volume', '>', 10],
						'and',
						[
							['ranked_serp_element.serp_item.type', '<>', 'paid'],
							'or',
							['ranked_serp_element.serp_item.is_paid', '=', false]
						]
					],
					'limit' => $config['limit']
				);
				$method = 'dataforseo_labs/google/ranked_keywords/live';
				break;
			case 'related_keywords':
				$keys = ['keyword', 'location', 'language', 'limit'];
				$post_array[] = array(
					'keyword' => $config['keywords'][0],
					'language_name' => $config['language'],
					'location_name' => $config['location'],
					'limit' => $config['limit']
				);
				$method = 'dataforseo_labs/google/related_keywords/live';
				break;
			case 'relevant_pages':
				$keys = ['url', 'location', 'language', 'limit'];
				$post_array[] = array(
					'target' => $config['url'][0],
					'language_name' => $config['language'],
					'location_name' => $config['location'],
					'filters' => [
						['metrics.organic.pos_1', '<>', 0],
						'or',
						['metrics.organic.pos_2_3', '<>', 0]
					],
					'limit' => $config['limit']
				);
				$method = 'dataforseo_labs/google/relevant_pages/live';
				break;
			case 'domain_analytics':
				$keys = ['url', 'location', 'language', 'limit'];
				$post_array[] = array(
					'target' => $config['url'][0],
					'language_name' => $config['language'],
					'location_name' => $config['location'],
					'limit' => $config['limit']
				);
				$method = 'domain_analytics/technologies/domain_technologies/live';
				break;
			case 'domain_analytics_from_keyword':
				$keys = ['keywords', 'location', 'language', 'limit'];
				$post_array[] = array(
					'search_terms' => $config['keywords'],
					'language_name' => $config['language'],
					'location_name' => $config['location'],
					'order_by' => ['last_visited,desc'],
					'limit' => $config['limit']
				);
				$method = 'domain_analytics/technologies/domains_by_html_terms/live';
				break;
			case 'google_ads':
				$keys = ['keywords', 'location', 'language', 'limit'];
				$post_array[] = array(
					'keywords' => $config['keywords'],
					'language_name' => $config['language'],
					'location_name' => $config['location'],
					'bid' => 999.00,
					'match' => 'exact',
					'limit' => $config['limit']
				);
				$method = 'keywords_data/google_ads/ad_traffic_by_keywords/live';
				break;
			case 'google_ads_from_url':
				$keys = ['url', 'location', 'language', 'limit'];
				$post_array[] = array(
					'target' => $config['url'][0],
					'language_name' => $config['language'],
					'location_name' => $config['location'],
					'order_by' => ['last_visited,desc'],
					'limit' => $config['limit']
				);
				$method = 'keywords_data/google_ads/keywords_for_site/live';
				break;
			case 'google_play_store':
				$keys = ['keywords', 'description', 'order_by', 'rating', 'limit'];
				$post_array[] = array(
				   'title' => $config['keywords'],
				   'description' => $config['description'],
				   'order_by' => [ 'item.rating.value,' . $config['order_by'] ],
				   'filters' => [
				      [ 'item.rating.value', '>', $config['rating'] ]
				   ],
				   'limit' => $config['limit']
				);
				$method = 'app_data/google/app_listings/search/live';
				break;
			default:
			throw new Exception(sprintf('The action "%s" is not supported by Youds Framework', $action));
		endswitch;

		foreach ($keys as $key => $value):
			if (!isset($config[$value])):
				
				throw new Exception(sprintf('Keys required for SEO call "%s" are %s', $action, print_r(str_replace(',', ', ', str_replace('[', '', str_replace(']', '', json_encode(array_values($keys))))), true)));
			endif;
		endforeach;
		try {
		   // POST /v3/app_data/google/app_listings/search/live
		   // POST /v3/app_data/apple/app_listings/search/live
		   // the full list of possible parameters is available in documentation
		   $result = $client->post('v3/' . $method, $post_array);
		   
		   // return results
		   return $result['tasks'][0]['result'];
		} catch (Exceptions\RestClient $e) {
		   echo "\n";
		   print "HTTP code: {$e->getHttpCode()}n";
		   print "Error code: {$e->getCode()}n";
		   print "Message: {$e->getMessage()}n";
		   print  $e->getTraceAsString();
		   echo "\n";
		}
		$client = null;
		
		
	}
	
}

?>