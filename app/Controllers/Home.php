<?php

namespace App\Controllers;
use CodeIgniter\Models\Controller;
use App\Models\M_z;

class Home extends BaseController
{
	public function index()
	{
		echo view('header');
		echo view('menu');
		echo view('footer');
	}

	private function log_activity($activity)
    {
		$model = new M_z();
        $data = [
            'id_user'    => session()->get('id'),
            'activity'   => $activity,
			'timestamp' => date('Y-m-d H:i:s'),
			'delete' => Null
        ];

        $model->tambah('activity', $data);
    }

	public function login(){
        $model = new M_z();
        $where5 = array('id_setting' => 1);
        $data['setting'] = $model->getwhere('setting', $where5);
		echo view('header', $data);
		echo view('login');
	}

	public function generateCaptcha()
{
    // Create a string of possible characters
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $captcha_code = '';
    
    // Generate a random CAPTCHA code with letters and numbers
    for ($i = 0; $i < 6; $i++) {
        $captcha_code .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    // Store CAPTCHA code in session
    session()->set('captcha_code', $captcha_code);
    
    // Create an image for CAPTCHA
    $image = imagecreate(120, 40); // Increased size for better readability
    $background = imagecolorallocate($image, 200, 200, 200);
    $text_color = imagecolorallocate($image, 0, 0, 0);
    $line_color = imagecolorallocate($image, 64, 64, 64);
    
    imagefilledrectangle($image, 0, 0, 120, 40, $background);
    
    // Add some random lines to the CAPTCHA image for added complexity
    for ($i = 0; $i < 5; $i++) {
        imageline($image, rand(0, 120), rand(0, 40), rand(0, 120), rand(0, 40), $line_color);
    }
    
    // Add the CAPTCHA code to the image
    imagestring($image, 5, 20, 10, $captcha_code, $text_color);
    
    // Output the CAPTCHA image
    header('Content-type: image/png');
    imagepng($image);
    imagedestroy($image);
}


public function aksi_login()
{
    // Periksa koneksi internet
    if (!$this->checkInternetConnection()) {
        // Jika tidak ada koneksi, cek CAPTCHA gambar
        $captcha_code = $this->request->getPost('captcha_code');
        if (session()->get('captcha_code') !== $captcha_code) {
            session()->setFlashdata('toast_message', 'Invalid CAPTCHA');
            session()->setFlashdata('toast_type', 'danger');
            return redirect()->to('home/login');
        }
    } else {
        // Jika ada koneksi, cek Google reCAPTCHA
        $recaptchaResponse = trim($this->request->getPost('g-recaptcha-response'));
        $secret = '6LeKfiAqAAAAAFkFzd_B9MmWjX76dhdJmJFb6_Vi'; // Ganti dengan Secret Key Anda
        $credential = array(
            'secret' => $secret,
            'response' => $recaptchaResponse
        );

        $verify = curl_init();
        curl_setopt($verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
        curl_setopt($verify, CURLOPT_POST, true);
        curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($credential));
        curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($verify);
        curl_close($verify);

        $status = json_decode($response, true);

        if (!$status['success']) {
            session()->setFlashdata('toast_message', 'Captcha validation failed');
            session()->setFlashdata('toast_type', 'danger');
            return redirect()->to('home/login');
        }
    }

    // Proses login seperti biasa
    $u = $this->request->getPost('username');
    $p = $this->request->getPost('password');

    $where = array(
        'username' => $u,
        'password' => md5($p),
    );
    $model = new M_z;
    $cek = $model->getWhere('user', $where);

    if ($cek) {
        // $this->log_activitys('User Mel$where5 = array('id_setting' => 1);
        session()->set('nama', $cek->username);
        session()->set('id', $cek->id_user);
        session()->set('level', $cek->level);
        return redirect()->to('home/');
    } else {
        session()->setFlashdata('toast_message', 'Invalid login credentials');
        session()->setFlashdata('toast_type', 'danger');
        return redirect()->to('home/login');
    }
}

public function checkInternetConnection()
{
    $connected = @fsockopen("www.google.com", 80);
    if ($connected) {
        fclose($connected);
        return true;
    } else {
        return false;
    }
}

public function logout()
{
    // $this->log_activity('User Logout');
    session()->destroy();
    return redirect()->to('home/login');
}

public function formd(){
    echo view('header');
    echo view('menu');
    echo view('formd');
    echo view('footer');
    
}

public function hitungDiskon()
{
    $harga = $this->request->getPost('harga');
    $diskon = $this->request->getPost('diskon');

    if ($harga && $diskon !== null) {
        $hargaAkhir = $harga - ($harga * $diskon / 100);
        return $this->response->setJSON(['harga_setelah_diskon' => number_format($hargaAkhir, 2)]);
    }

    return $this->response->setJSON(['error' => 'Harap masukkan harga dan diskon dengan benar!']);
}

public function updateMenuVisibilityAjax()
{
    // Get data from the AJAX request
    $menu = $this->request->getPost('menu'); // e.g., 'data', 'dashboard'
    $level = $this->request->getPost('level'); // e.g., 1, 2, 3
    $visibility = $this->request->getPost('visibility'); // 1 or 0

    // Logging the data received from AJAX request
    log_message('debug', 'Received data from AJAX - Menu: ' . $menu . ', Level: ' . $level . ', Visibility: ' . $visibility);

    // Prepare data for the update
    $updateData = [$menu => $visibility];
    $whereCondition = ['level' => $level];

    // Logging the prepared data for the update
    log_message('debug', 'Update Data: ' . json_encode($updateData));
    log_message('debug', 'Where Condition: ' . json_encode($whereCondition));

    // Initialize the model
    $menuModel = new M_z();

    // Call the model method to update the menu visibility
    $result = $menuModel->updateMenuVisibility('menu', $updateData, $whereCondition);

    // Check if the update was successful and log the result
    if ($result) {
        log_message('debug', 'Menu visibility updated successfully.');
        return $this->response->setJSON(['status' => 'success', 'message' => 'Menu visibility updated successfully.']);
    } else {
        log_message('error', 'Failed to update menu visibility.');
        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update menu visibility.']);
    }
}

public function aksi_edit_website()
{
    // Load the model that interacts with your settings
    $model = new M_z(); // Replace M_p with the actual model name

    // Retrieve the settings from the database
    $where5 = array('id_setting' => 1);
    $setting = $model->getwhere('setting',$where5); // Assuming you have a method to get current settings

    // Get the name from the request
    $name = $this->request->getPost('name');

    $icon = $this->request->getFile('icon');

    // Array to hold image names
    $images = [];

    // Check and upload icon
    if ($icon && $icon->isValid()) {
        $images['icon'] = $icon->getName();
        $model->uploadimages($icon); // Call uploadimages from the model
    } else {
        // Keep the existing icon name if no new file is uploaded
        $images['icon'] = $setting->logo;
    }



    // Update the settings in the database with the new image names and the new name
    $model->updateSettings($name, $images['icon']); // Corrected parameter usage

    return redirect()->to('home/Website'); // Redirect after processing
}

public function filteruserlog() {
    $model = new M_z(); // Make sure to replace with your actual model for logs
    $idUser = $this->request->getGet('id_user'); // Get the selected user ID from the query string

    // Fetch users for the filter dropdown
    $data['users'] = $model->tampil('user'); // Adjust this method based on how you retrieve users

    // Get logs based on user filter
    if ($idUser) {
        $where = array('activity.id_user' => $idUser, 'activity.delete' => Null);
        $data['log'] = $model->join1where1('activity','user','activity.id_User = user.id_user',$where); // Method to get logs for a specific user
    } else {
        $data['log'] = $model->join1('activity','user','activity.id_User = user.id_user'); // Fetch all logs if no user is selected
    }
    $data['logss'] = $model->join1('activity','user','activity.id_User = user.id_user'); // Fetch all logs if no user is selected
    $where5 = array('id_setting' => 1);
    $data['setting'] = $model->getwhere('setting', $where5);
    $where6 = array('level' => session()->get('level'));
        $data['menu'] = $model->getwhere('menu', $where6);
        if ($data['menu']->datas == 1) {
    echo view('header',$data);
    echo view('menu',$data);
    echo view('activitylog', $data);
    echo view('footer');
}else{
    return redirect()->to('home/login');
}
}

public function LogActivity(){
    $model = new M_z();
    $where1 = array('activity.delete' => null);
    $data['log'] = $model->join1where1('activity','user','activity.id_user = user.id_user',$where1);
    $data['menus'] = $model->tampil('menu');
    $data['users'] = $model->tampil('user');
    $where5 = array('id_setting' => 1);
    $data['setting'] = $model->getwhere('setting', $where5);
    $where6 = array('level' => session()->get('level'));
        $data['menu'] = $model->getwhere('menu', $where6);
        $this->log_activity('User membuka Setting Website');
        if ($data['menu']->datas == 1) {
    echo view('header', $data);
    echo view('menu', $data);
    echo view('activitylog', $data);
    echo view('footer');
}else{
    return redirect()->to('home/login');
}
}

public function Website(){
    $model = new M_z();
    $data['menus'] = $model->tampil('menu');
    $where5 = array('id_setting' => 1);
    $data['setting'] = $model->getwhere('setting', $where5);
    $where6 = array('level' => session()->get('level'));
        $data['menu'] = $model->getwhere('menu', $where6);
        $this->log_activity('User membuka Setting Website');
        if ($data['menu']->setting == 1) {
    echo view('header', $data);
    echo view('menu', $data);
    echo view('website', $data);
    echo view('footer');
}else{
    return redirect()->to('home/login');
}
}

public function MenuManage(){
    $model = new M_z();
    $data['menus'] = $model->tampil('menu');
    $where5 = array('id_setting' => 1);
    $data['setting'] = $model->getwhere('setting', $where5);
    $where6 = array('level' => session()->get('level'));
        $data['menu'] = $model->getwhere('menu', $where6);
        $this->log_activity('User membuka Manage Menu');
        if ($data['menu']->setting == 1) {
    echo view('header', $data);
    echo view('menu', $data);
    echo view('menu_manage', $data);
    echo view('footer');
}else{
    return redirect()->to('home/login');
}
}

public function user(){
    $model = new M_z();
    $where5 = array('user.deleted' => Null);
    $data['user'] = $model->join1where1('user','level','user.level = level.id_level', $where5);
    echo view('header', $data);
    echo view('menu', $data);
    echo view('user', $data);
    echo view('footer');
}

public function TambahUser(){
    $model = new M_z();
    $where6 = array('level' => session()->get('level'));
    $data['menu'] = $model->getwhere('menu', $where6);
   

    $model = new M_z();
    $data['menus'] = $model->tampil('menu');
    $data['level'] = $model->tampil('level');
    // Ambil setting dari model
    $where5 = array('id_setting' => 1);
    $data['setting'] = $model->getwhere('setting', $where5);
    $this->log_activity('User membuka Kategori');


    echo view('header', $data);
    echo view('menu', $data);
    echo view('tambahuser', $data);
    echo view('footer');

}


public function aksi_tambah_User() {
    $model = new M_z();
    
    // Ambil data dari form
    $user = $this->request->getPost('nama_user');
    $level = $this->request->getPost('level');
    $email = $this->request->getPost('email');
    // Set password default
    $password = md5('sph');
    
    // Menyusun data yang akan dimasukkan ke dalam database
    $data = [
        'username' => $user,
        'password' => $password,
        'level' => $level,
        'email' => $email,
        'created_at' => date('Y-m-d H:i:s')
    ];

    // Menambahkan data user ke dalam tabel 'user'
    $model->tambah('user', $data);

    // Mengalihkan ke halaman daftar user setelah berhasil
    return redirect()->to('home/User');
}

public function EditUser($id){
    $model = new M_z();
    $where6 = array('level' => session()->get('level'));
    $data['menu'] = $model->getwhere('menu', $where6);

    $model = new M_z();
    $data['menus'] = $model->tampil('menu');
    
    // Ambil setting dari model
    $where5 = array('id_setting' => 1);
    $data['setting'] = $model->getwhere('setting', $where5);
    $this->log_activity('User membuka Kategori');
    
    // Ambil kategori dari model
    $where = array('deleted' => Null);
    $where1 = array('id_user' => $id);
    $data['user'] = $model->tampilwhere2Row('user', $where, $where1);
    $data['level'] = $model->tampil('level');

    echo view('header', $data);
    echo view('menu', $data);
    echo view('edituser', $data);
    echo view('footer');

}

public function aksi_edit_User() {
    $model = new M_z();
    
    // Ambil data dari form
    $user = $this->request->getPost('nama_user');
    $level = $this->request->getPost('level');
    $email = $this->request->getPost('email'); // Only for Siswa
    $id = $this->request->getPost('id');

    // Menyusun data yang akan dimasukkan ke dalam database
    $data = [
        'username' => $user,
        'level' => $level,
        'email' => $email,  // If level is Siswa, update id_kelas
    ];

    // If password is being updated
    $password = $this->request->getPost('password');
    if (!empty($password)) {
        $data['password'] = md5($password);  // Update password if provided
    }

    $where = ['id_user' => $id];

    // Menyimpan perubahan data user ke dalam database
    $model->edit('user', $data, $where);

    // Mengalihkan ke halaman daftar user setelah berhasil
    return redirect()->to('home/user');
}

public function SDuser($id){
    $model = new M_z();
    $data = [
        'deleted' => date('Y-m-d H:i:s')
    ];
    $where = array('id_user' => $id);
    $model->edit('user', $data, $where);
    return redirect()->to('home/user');
}

}
