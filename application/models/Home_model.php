<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Home_model extends MY_Model 
{
    public $table = 'barang_masuk';

    /**
     * Get last 6 month labels in Indonesian format
     */
    public function getMonthlyLabels()
    {
        $labels = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = date('Y-m-01', strtotime("-$i months"));
            $labels[] = date('M Y', strtotime($date));
        }
        return json_encode($labels);
    }

    /**
     * Get count of barang_masuk per month for last 6 months
     */
    public function getMonthlyMasuk()
    {
        $counts = [];
        for ($i = 5; $i >= 0; $i--) {
            $start = date('Y-m-01', strtotime("-$i months"));
            $end   = date('Y-m-t', strtotime("-$i months"));
            $this->db->where('waktu >=', $start . ' 00:00:00');
            $this->db->where('waktu <=', $end . ' 23:59:59');
            $counts[] = $this->db->count_all_results('barang_masuk');
        }
        return json_encode($counts);
    }

    /**
     * Get count of barang_keluar per month for last 6 months
     */
    public function getMonthlyKeluar()
    {
        $counts = [];
        for ($i = 5; $i >= 0; $i--) {
            $start = date('Y-m-01', strtotime("-$i months"));
            $end   = date('Y-m-t', strtotime("-$i months"));
            $this->db->where('waktu >=', $start . ' 00:00:00');
            $this->db->where('waktu <=', $end . ' 23:59:59');
            $counts[] = $this->db->count_all_results('barang_keluar');
        }
        return json_encode($counts);
    }
}

/* End of file Home_model.php */
