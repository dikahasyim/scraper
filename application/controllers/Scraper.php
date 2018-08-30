<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Scraper extends CI_Controller {
    
    public function __construct()
    {        
        parent::__construct();
    }
	public function index(){
		$this->load->view('welcome_message');
	
	}
    
	public function s()
    {
        $data = array();
        if(isset($_GET['search']))
        {
            $data['search'] = trim($_GET['search']);
            if(!empty( $data['search']))
            {
				
				$term = str_replace(' ','+',$data['search']);
				$term = str_replace("'",'',$term);
				$data['init'] = "https://redsky.target.com/v1/plp/search/?count=1&offset=0&keyword=".$term."&default_purchasability_filter=true&store_ids=1401%2C2212%2C3230%2C1849%2C1344%2C3276%2C2850%2C3284%2C3249%2C2451%2C3229%2C2840%2C3277%2C1150%2C1886%2C3237%2C2380%2C2811%2C2475%2C1263&visitorId=01658451049802018E59A9700CBCC130&pageId=%2Fs%2F".$term."&channel=web";
                $init_result = json_decode(file_get_contents($data['init']));
				$found_items = (int)$init_result->search_response->metaData[4]->value;
				
				
				$count = (!empty($_GET['count'])) ? $_GET['count'] : 10;
				$k = (!empty($_GET['offset'])) ? $_GET['offset'] : 0;
				$offset= $k+1;
				$json = array("search_term" => $data['search'], "items_found" => $found_items, "next_page" => base_url('scraper/')."s?search=".$term."&count=".$count."&offset=".$offset);
				$data['url'] = "https://redsky.target.com/v1/plp/search/?count=".$count."&offset=".$k."&keyword=".$term."&default_purchasability_filter=true&store_ids=1401%2C2212%2C3230%2C1849%2C1344%2C3276%2C2850%2C3284%2C3249%2C2451%2C3229%2C2840%2C3277%2C1150%2C1886%2C3237%2C2380%2C2811%2C2475%2C1263&visitorId=01658451049802018E59A9700CBCC130&pageId=%2Fs%2F".$term."&channel=web";
					$result = json_decode(file_get_contents($data['url']));
					$found_items1 = count($result->search_response->items->Item);
					for($i = 0 ; $i < $found_items1 ; $i++){
						$list = $result->search_response->items->Item[$i];
						
						$title = (isset($list->title)) ? $list->title : null ;
						$tcin = (isset($list->tcin)) ? $list->tcin : null;
						$url = (isset($list->url)) ? "https://www.target.com".$list->url : null;
						$description = (isset($list->description)) ? $list->description : null;
						$merch_sub_class = (isset($list->merch_sub_class)) ? $list->merch_sub_class : null;
						$merch_class = (isset($list->merch_class)) ? $list->merch_class : null;
						$merch_class_id = (isset($list->merch_class_id)) ? $list->merch_class_id : null;
						$brand = (isset($list->brand)) ? $list->brand : null;
						$image = (isset($list->images[0]->base_url)) ? $list->images[0]->base_url.$list->images[0]->primary : null;
						$availability_status = (isset($list->availability_status)) ? $list->availability_status : null;
						$pick_up_in_store = (isset($list->pick_up_in_store)) ? $list->pick_up_in_store : null;
						$ship_to_store = (isset($list->ship_to_store)) ? $list->ship_to_store : null;
						$rush_delivery = (isset($list->rush_delivery)) ? $list->rush_delivery : null;
						$is_out_of_stock_in_all_store_locations = (isset($list->is_out_of_stock_in_all_store_locations)) ? $list->is_out_of_stock_in_all_store_locations : null;
						$is_out_of_stock_in_all_online_locations = (isset($list->is_out_of_stock_in_all_online_locations)) ? $list->is_out_of_stock_in_all_online_locations : null;
						$list_price = (isset($list->list_price->formatted_price)) ? $list->list_price->formatted_price : null;
						$average_rating = (isset($list->average_rating)) ? $list->average_rating  : null;
						
						$top_reviews = array();
						$top_reviews_count = (isset($list->top_reviews)) ? count($list->top_reviews) : 0;;
						for($j = 0 ; $j < $top_reviews_count ; $j++){
							$user_nickname = (isset($list->top_reviews[$j]->user_nickname)) ? $list->top_reviews[$j]->user_nickname: null;
							$title_review = (isset($list->top_reviews[$j]->title)) ? $list->top_reviews[$j]->title: null;
							$rating = (isset($list->top_reviews[$j]->rating)) ? $list->top_reviews[$j]->rating: null;
							$rating_range = (isset($list->top_reviews[$j]->rating_range)) ? $list->top_reviews[$j]->rating_range: null;
							$review_text = (isset($list->top_reviews[$j]->review_text)) ? $list->top_reviews[$j]->review_text: null;
							$last_moderated_time = (isset($list->top_reviews[$j]->last_moderated_time)) ? $list->top_reviews[$j]->last_moderated_time: null;
							$top_reviews_list = array("user_nickname" => $user_nickname,
													"title_review" => $title_review,
													"rating" => $rating,
													"rating_range" => $rating_range,
													"review_text" => $review_text,
													"last_moderated_time" => $last_moderated_time
											);
							array_push($top_reviews,$top_reviews_list);
						}
						
						$bullet_description = array();
						$bullet_description_count = (isset($list->bullet_description)) ? count($list->bullet_description) : 0;
						for($l = 0 ; $l < $bullet_description_count ; $l++){
							array_push($bullet_description, $list->bullet_description[$l]);
						}
						
						//Scrap for Q&A
						$qna = array();
						$data['qna'] = "https://redsky.target.com/drax-domain-api/v1/questions?product_id=" . $tcin  . "&sort_order=recent_answered&limit=10&offset=0";
						$qna_result = json_decode(file_get_contents($data['qna']));
						$found_qna = count($qna_result);
						for($m = 0 ; $m < $found_qna ; $m++){
							$list_qna = $qna_result[$m];
							
							$UserNickname = (isset($list_qna->UserNickname)) ? $list_qna->UserNickname : null;
							$QuestionSummary = (isset($list_qna->QuestionSummary)) ? $list_qna->QuestionSummary : null;
							$ModerationStatus = (isset($list_qna->ModerationStatus)) ? $list_qna->ModerationStatus : null;
							$SubmissionTime = (isset($list_qna->SubmissionTime)) ? $list_qna->SubmissionTime : null;
							$LastModificationTime = (isset($list_qna->LastModificationTime)) ? $list_qna->LastModificationTime : null;
							$LastModeratedTime = (isset($list_qna->LastModeratedTime)) ? $list_qna->LastModeratedTime : null;
						
							$answer_UserNickname = (isset($qna_result[$m]->Answers[0]->UserNickname)) ? $qna_result[$m]->Answers[0]->UserNickname : null;
							$answer_AnswerText = (isset($qna_result[$m]->Answers[0]->AnswerText)) ? $qna_result[$m]->Answers[0]->AnswerText : null;
							$answer_ModerationStatus = (isset($qna_result[$m]->Answers[0]->ModerationStatus)) ? $qna_result[$m]->Answers[0]->ModerationStatus : null;
							$answer_SubmissionTime = (isset($qna_result[$m]->Answers[0]->SubmissionTime)) ? $qna_result[$m]->Answers[0]->SubmissionTime : null;
							$answer_LastModeratedTime = (isset($qna_result[$m]->Answers[0]->LastModeratedTime)) ? $qna_result[$m]->Answers[0]->LastModeratedTime : null;
							$answer_LastModificationTime = (isset($qna_result[$m]->Answers[0]->LastModificationTime)) ? $qna_result[$m]->Answers[0]->LastModificationTime : null;
							$qna_list = array("UserNickname" => $UserNickname,
												"QuestionSummary" => $QuestionSummary,
												"ModerationStatus" => $ModerationStatus,
												"SubmissionTime" => $SubmissionTime,
												"LastModificationTime" => $LastModificationTime,
												"LastModeratedTime" => $LastModeratedTime,
												"answer_UserNickname" => $answer_UserNickname,
												"answer_AnswerText" => $answer_AnswerText,
												"answer_ModerationStatus" => $answer_ModerationStatus,
												"answer_SubmissionTime" => $answer_SubmissionTime,
												"answer_LastModeratedTime" => $answer_LastModeratedTime,
												"answer_LastModificationTime" => $answer_LastModificationTime
												);
							array_push($qna,$qna_list);
						}
						
						
						$goods = array("title" => $title);
						$goods_detil = array("product_id" => $tcin, 
												"url"=>$url,
												"description" => $description,
												"merch_sub_class" => $merch_sub_class,
												"merch_class" => $merch_class,
												"merch_class_id" => $merch_class_id,
												"brand" => $brand,
												"image" => $image,
												"availability_status" => $availability_status,
												"pick_up_in_store" => $pick_up_in_store,
												"ship_to_store" => $ship_to_store,
												"rush_delivery" => $rush_delivery,
												"is_out_of_stock_in_all_store_locations" => $is_out_of_stock_in_all_store_locations,
												"is_out_of_stock_in_all_online_locations" => $is_out_of_stock_in_all_online_locations,
												"list_price" => $list_price,
												"average_rating" => $average_rating,
												"top_reviews" => $top_reviews,
												"bullet_description" => $bullet_description,
												"qna" => $qna
											);
						array_push($goods,$goods_detil);
						array_push($json,$goods);
					}
				
				header('Content-Type: application/json');
				echo json_encode($json);
            }else{
                $json = array("search_term" => "", "items_found" => 0);
				header('Content-Type: application/json');
				echo json_encode($json);
            }
        }
    }
	
	public function s2()
    {
        $data = array();
        if(isset($_GET['search']))
        {
            $data['search'] = trim($_GET['search']);
            if(!empty( $data['search']))
            {
				$term = str_replace(' ','+',$data['search']);
				
				$data['init'] = "https://redsky.target.com/v1/plp/search/?count=1&offset=0&keyword=".$term."&default_purchasability_filter=true&store_ids=1401%2C2212%2C3230%2C1849%2C1344%2C3276%2C2850%2C3284%2C3249%2C2451%2C3229%2C2840%2C3277%2C1150%2C1886%2C3237%2C2380%2C2811%2C2475%2C1263&visitorId=01658451049802018E59A9700CBCC130&pageId=%2Fs%2F".$term."&channel=web";
                $init_result = json_decode(file_get_contents($data['init']));
				$found_items = (int)$init_result->search_response->metaData[4]->value;
				
				$offset = ($found_items > 96) ? floor($found_items / 96) : 0;
				$json = array("search_term" => $data['search'], "items_found" => $found_items);
				
				for($k = 0 ; $k <= $offset ; $k++){
					$count = ($k < $offset) ? 96 : $found_items % 96;
					$data['url'] = "https://redsky.target.com/v1/plp/search/?count=".$count."&offset=".$k."&keyword=".$term."&default_purchasability_filter=true&store_ids=1401%2C2212%2C3230%2C1849%2C1344%2C3276%2C2850%2C3284%2C3249%2C2451%2C3229%2C2840%2C3277%2C1150%2C1886%2C3237%2C2380%2C2811%2C2475%2C1263&visitorId=01658451049802018E59A9700CBCC130&pageId=%2Fs%2F".$term."&channel=web";
					$result = json_decode(file_get_contents($data['url']));
					$found_items1 = count($result->search_response->items->Item);
					for($i = 0 ; $i < $found_items1 ; $i++){
						$list = $result->search_response->items->Item[$i];
						
						$title = (isset($list->title)) ? $list->title : null ;
						$tcin = (isset($list->tcin)) ? $list->tcin : null;
						$url = (isset($list->url)) ? "https://www.target.com".$list->url : null;
						$description = (isset($list->description)) ? $list->description : null;
						$merch_sub_class = (isset($list->merch_sub_class)) ? $list->merch_sub_class : null;
						$merch_class = (isset($list->merch_class)) ? $list->merch_class : null;
						$merch_class_id = (isset($list->merch_class_id)) ? $list->merch_class_id : null;
						$brand = (isset($list->brand)) ? $list->brand : null;
						$image = (isset($list->images[0]->base_url)) ? $list->images[0]->base_url.$list->images[0]->primary : null;
						$availability_status = (isset($list->availability_status)) ? $list->availability_status : null;
						$pick_up_in_store = (isset($list->pick_up_in_store)) ? $list->pick_up_in_store : null;
						$ship_to_store = (isset($list->ship_to_store)) ? $list->ship_to_store : null;
						$rush_delivery = (isset($list->rush_delivery)) ? $list->rush_delivery : null;
						$is_out_of_stock_in_all_store_locations = (isset($list->is_out_of_stock_in_all_store_locations)) ? $list->is_out_of_stock_in_all_store_locations : null;
						$is_out_of_stock_in_all_online_locations = (isset($list->is_out_of_stock_in_all_online_locations)) ? $list->is_out_of_stock_in_all_online_locations : null;
						$list_price = (isset($list->list_price->formatted_price)) ? $list->list_price->formatted_price : null;
						$average_rating = (isset($list->average_rating)) ? $list->average_rating  : null;
						
						$top_reviews = array();
						$top_reviews_count = (isset($list->top_reviews)) ? count($list->top_reviews) : 0;;
						for($j = 0 ; $j < $top_reviews_count ; $j++){
							$user_nickname = (isset($list->top_reviews[$j]->user_nickname)) ? $list->top_reviews[$j]->user_nickname: null;
							$title_review = (isset($list->top_reviews[$j]->title)) ? $list->top_reviews[$j]->title: null;
							$rating = (isset($list->top_reviews[$j]->rating)) ? $list->top_reviews[$j]->rating: null;
							$rating_range = (isset($list->top_reviews[$j]->rating_range)) ? $list->top_reviews[$j]->rating_range: null;
							$review_text = (isset($list->top_reviews[$j]->review_text)) ? $list->top_reviews[$j]->review_text: null;
							$last_moderated_time = (isset($list->top_reviews[$j]->last_moderated_time)) ? $list->top_reviews[$j]->last_moderated_time: null;
							$top_reviews_list = array("user_nickname" => $user_nickname,
													"title_review" => $title_review,
													"rating" => $rating,
													"rating_range" => $rating_range,
													"review_text" => $review_text,
													"last_moderated_time" => $last_moderated_time
											);
							array_push($top_reviews,$top_reviews_list);
						}
						
						$bullet_description = array();
						$bullet_description_count = (isset($list->bullet_description)) ? count($list->bullet_description) : 0;
						for($l = 0 ; $l < $bullet_description_count ; $l++){
							array_push($bullet_description, $list->bullet_description[$l]);
						}
						
						//Scrap for Q&A
						$qna = array();
						$data['qna'] = "https://redsky.target.com/drax-domain-api/v1/questions?product_id=" . $tcin  . "&sort_order=recent_answered&limit=10&offset=0";
						$qna_result = json_decode(file_get_contents($data['qna']));
						$found_qna = count($qna_result);
						for($m = 0 ; $m < $found_qna ; $m++){
							$list_qna = $qna_result[$m];
							
							$UserNickname = (isset($list_qna->UserNickname)) ? $list_qna->UserNickname : null;
							$QuestionSummary = (isset($list_qna->QuestionSummary)) ? $list_qna->QuestionSummary : null;
							$ModerationStatus = (isset($list_qna->ModerationStatus)) ? $list_qna->ModerationStatus : null;
							$SubmissionTime = (isset($list_qna->SubmissionTime)) ? $list_qna->SubmissionTime : null;
							$LastModificationTime = (isset($list_qna->LastModificationTime)) ? $list_qna->LastModificationTime : null;
							$LastModeratedTime = (isset($list_qna->LastModeratedTime)) ? $list_qna->LastModeratedTime : null;
						
							$answer_UserNickname = (isset($qna_result[$m]->Answers[0]->UserNickname)) ? $qna_result[$m]->Answers[0]->UserNickname : null;
							$answer_AnswerText = (isset($qna_result[$m]->Answers[0]->AnswerText)) ? $qna_result[$m]->Answers[0]->AnswerText : null;
							$answer_ModerationStatus = (isset($qna_result[$m]->Answers[0]->ModerationStatus)) ? $qna_result[$m]->Answers[0]->ModerationStatus : null;
							$answer_SubmissionTime = (isset($qna_result[$m]->Answers[0]->SubmissionTime)) ? $qna_result[$m]->Answers[0]->SubmissionTime : null;
							$answer_LastModeratedTime = (isset($qna_result[$m]->Answers[0]->LastModeratedTime)) ? $qna_result[$m]->Answers[0]->LastModeratedTime : null;
							$answer_LastModificationTime = (isset($qna_result[$m]->Answers[0]->LastModificationTime)) ? $qna_result[$m]->Answers[0]->LastModificationTime : null;
							$qna_list = array("UserNickname" => $UserNickname,
												"QuestionSummary" => $QuestionSummary,
												"ModerationStatus" => $ModerationStatus,
												"SubmissionTime" => $SubmissionTime,
												"LastModificationTime" => $LastModificationTime,
												"LastModeratedTime" => $LastModeratedTime,
												"answer_UserNickname" => $answer_UserNickname,
												"answer_AnswerText" => $answer_AnswerText,
												"answer_ModerationStatus" => $answer_ModerationStatus,
												"answer_SubmissionTime" => $answer_SubmissionTime,
												"answer_LastModeratedTime" => $answer_LastModeratedTime,
												"answer_LastModificationTime" => $answer_LastModificationTime
												);
							array_push($qna,$qna_list);
						}
						
						
						$goods = array("title" => $title);
						$goods_detil = array("product_id" => $tcin, 
												"url"=>$url,
												"description" => $description,
												"merch_sub_class" => $merch_sub_class,
												"merch_class" => $merch_class,
												"merch_class_id" => $merch_class_id,
												"brand" => $brand,
												"image" => $image,
												"availability_status" => $availability_status,
												"pick_up_in_store" => $pick_up_in_store,
												"ship_to_store" => $ship_to_store,
												"rush_delivery" => $rush_delivery,
												"is_out_of_stock_in_all_store_locations" => $is_out_of_stock_in_all_store_locations,
												"is_out_of_stock_in_all_online_locations" => $is_out_of_stock_in_all_online_locations,
												"list_price" => $list_price,
												"average_rating" => $average_rating,
												"top_reviews" => $top_reviews,
												"bullet_description" => $bullet_description,
												"qna" => $qna
											);
						array_push($goods,$goods_detil);
						array_push($json,$goods);
					}
				}
				//array_push($json,$goods);
				//print_r($json);
				header('Content-Type: application/json');
				echo json_encode($json);
            }else{
                $json = array("search_term" => "", "items_found" => 0);
				header('Content-Type: application/json');
				echo json_encode($json);
            }
        }
    }
}