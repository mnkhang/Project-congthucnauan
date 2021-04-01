<?php

class Tricks extends CIF_Controller {

    public $layout = 'full';
    public $module = 'tricks';
    public $model = 'Blog_model';

    public function __construct() {
        parent::__construct();
        $this->load->model($this->model);
        $this->_primary_key = $this->{$this->model}->_primary_keys[0];
    }

    public function index($offset = 0) {
        //Pagination
        $this->load->library('pagination');
        config('pagination_limit', 8);
        $config['total_rows'] = $this->{$this->model}->get(TRUE);
        $config['base_url'] = site_url('tricks/index');
        $config['per_page'] = config('pagination_limit');
        if ($this->uri->segment(2))
            $this->db->offset = $offset;
        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();
        $this->db->limit($config['per_page'], $offset);

        config('title', lang("global_Tips_and_Tricks") . ' | ' . config('title'));

        $data['items'] = $this->db
                        ->order_by('updated', 'desc')
                        ->get('blog')->result();
        $data['popular'] = $this->db
                        ->limit('8')
                        ->order_by('visits', 'desc')
                        ->get('blog')->result();
        $this->load->view($this->module, $data);
    }

    public function trick($permalink) {

        $data = array();
        if (!$permalink)
            show_404();

        $this->db->where('permalink', $permalink)->set("visits", "visits + 1", FALSE)->update('blog');

        $data['item'] = $this->db->where("permalink", urldecode($permalink))->get("blog")->row();
        if (!$data['item'])
            show_404();
        config('title', $data['item']->title . ' | ' . config('title'));
        config('meta_description', $data['item']->meta_description);

        $data['tricks'] = $this->db
                        ->limit('6')
                        ->order_by('blog_id', 'desc')
                        ->where('blog.permalink !=', urldecode($permalink))
                        ->get('blog')->result();
        $data['popular'] = $this->db
                        ->limit('6')
                        ->order_by('visits', 'desc')
                        ->where('blog.permalink !=', urldecode($permalink))
                        ->get('blog')->result();
        $this->load->view('trick', $data);
    }

}
