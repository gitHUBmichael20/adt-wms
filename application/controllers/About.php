<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller About - Dinonaktifkan (halaman Extra telah dihapus)
 */
class About extends MY_Controller 
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        // Halaman About telah dihapus, redirect ke dashboard
        redirect(base_url('home'));
    }
}

/* End of file About.php */
