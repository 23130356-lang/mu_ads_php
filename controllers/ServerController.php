<?php

require_once '../models/Server.php';



class ServerController {

    private $serverModel;

    private $db;



    public function __construct($db) {

        $this->db = $db;

        $this->serverModel = new Server($db);

    }



    private function handleBannerUpload() {

        $uploadType = $_POST['uploadType'] ?? 'file';

        $bannerString = '';



        if ($uploadType === 'file') {

            if (isset($_FILES['banner_file']) && $_FILES['banner_file']['error'] === 0) {



                $targetDir = "../public/uploads/";

                if (!file_exists($targetDir)) {

                    mkdir($targetDir, 0777, true);

                }



                $fileType = strtolower(pathinfo($_FILES["banner_file"]["name"], PATHINFO_EXTENSION));

                $newFileName = time() . '_' . rand(1000,9999) . '.' . $fileType;

                $targetFilePath = $targetDir . $newFileName;



                $allowTypes = ['jpg','jpeg','png','gif','webp'];



                if (in_array($fileType, $allowTypes) && $_FILES["banner_file"]["size"] < 5*1024*1024) {

                    if (move_uploaded_file($_FILES["banner_file"]["tmp_name"], $targetFilePath)) {

                        $bannerString = "uploads/" . $newFileName;

                    }

                }

            }

        } else {

            $url = trim($_POST['banner_url'] ?? '');

            if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {

                $bannerString = $url;

            }

        }



        return $bannerString;

    }



    public function store() {

        if (session_status() === PHP_SESSION_NONE) session_start();



        $userId = $_SESSION['user_id'] ?? 1;



        $finalBanner = $this->handleBannerUpload();



        $data = [

            'user_id'       => $userId,

            'server_name'   => trim($_POST['server_name'] ?? ''),

            'mu_name'       => trim($_POST['mu_name'] ?? ''),

            'slogan'        => trim($_POST['slogan'] ?? ''),

            'website_url'   => trim($_POST['website_url'] ?? ''),

            'fanpage_url'   => trim($_POST['fanpage_url'] ?? ''),

            'description'   => trim($_POST['description'] ?? ''),



            // Banner

            'banner_image'  => $finalBanner,

            'banner_package'=> $_POST['banner_package'] ?? 'BASIC',



            // Config

            'version_id'    => $_POST['version_id'] ?? null,

            'type_id'       => $_POST['type_id'] ?? null,

            'reset_id'      => $_POST['reset_id'] ?? null,

            'point_id'      => $_POST['point_id'] ?? null,

            'exp_rate'      => $_POST['exp_rate'] ?? 0,

            'drop_rate'     => $_POST['drop_rate'] ?? 0,

            'anti_hack'     => trim($_POST['anti_hack'] ?? ''),



            // Schedule (KHỚP MODEL)

            'alpha_date'    => $_POST['alpha_date'] ?? null,

            'alpha_time'    => $_POST['alpha_time'] ?? null,

            'open_date'     => $_POST['beta_date'] ?? null,

            'open_time'     => $_POST['beta_time'] ?? null

        ];



        $result = $this->serverModel->createFull($data);



        header("Location: create_server.php?status=" . ($result ? "success" : "error"));

        exit;

    }

}

?>