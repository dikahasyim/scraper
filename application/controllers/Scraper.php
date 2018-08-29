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
				/*$data['long_url'] = "https://www.target.com/s?searchTerm=".$term; */
				$data['long_url'] = "https://redsky.target.com/v1/plp/search/?count=24&offset=0&keyword=".$term."&default_purchasability_filter=true&store_ids=1401%2C2212%2C3230%2C1849%2C1344%2C3276%2C2850%2C3284%2C3249%2C2451%2C3229%2C2840%2C3277%2C1150%2C1886%2C3237%2C2380%2C2811%2C2475%2C1263&visitorId=01658451049802018E59A9700CBCC130&pageId=%2Fs%2F".$term."&channel=web";
                $this->load->library('scrapers');
				//$data['result'] = $this->scrapers->page($data['long_url']); // to scrape simple webpages [do not require cookie]
                $data['result'] = $this->scrapers->shDom($data['long_url']); // to scrape complex webpages [require cookie]
            }else{
                $data['err'] = 'Field is empty!!!';
            }
        }
        $this->load->view('welcome_message', $data);
    }
}