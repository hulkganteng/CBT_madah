<?php
// Runnable unit/integration check for Multi-role and TU design
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'CLI-Test';
$_SERVER['CI_ENV'] = 'development';

ob_start();
require 'index.php';
ob_end_clean();

$ci =& get_instance();
$ci->load->library('ion_auth');
$ci->load->model('Users_model', 'users');

echo "=== START TEST: MULTI-ROLE & TU ===\n";

// 1. Verifikasi tabel groups memiliki 'tu'
$tuGroup = $ci->db->get_where('groups', array('name' => 'tu'))->row();
assert(!empty($tuGroup), "Group 'tu' harus ada di database.");
echo "[PASS] Group 'tu' terdaftar (id: {$tuGroup->id})\n";

// 2. Ambil group IDs
$adminGroup = $ci->db->get_where('groups', array('name' => 'admin'))->row();
$guruGroup = $ci->db->get_where('groups', array('name' => 'guru'))->row();
$siswaGroup = $ci->db->get_where('groups', array('name' => 'siswa'))->row();

// Cari user untuk pengujian
$testUser = $ci->db->where('username !=', 'admin')->get('users')->row();
if (!$testUser) {
    // Buat dummy user jika tidak ada user staf kedua
    $testUserId = $ci->ion_auth->register('test_staf', 'password123', 'test_staf@test.local', array('first_name' => 'Test', 'last_name' => 'Staf'), array($guruGroup->id));
} else {
    $testUserId = $testUser->id;
}

// 3. Test sinkronisasi multi-role: Guru + TU
$res = $ci->users->syncUserGroups($testUserId, array($guruGroup->id, $tuGroup->id));
assert($res['status'] === TRUE, "Harus sukses sync Guru + TU.");
$groups = $ci->ion_auth->get_users_groups($testUserId)->result();
$groupNames = array_map(function($g) { return $g->name; }, $groups);
assert(in_array('guru', $groupNames, TRUE), "User harus punya role guru.");
assert(in_array('tu', $groupNames, TRUE), "User harus punya role tu.");
echo "[PASS] Sinkronisasi multi-role Guru+TU berhasil.\n";

// 4. Test aturan eksklusif Siswa: Siswa + Guru -> DITOLAK
$resSiswaConflict = $ci->users->syncUserGroups($testUserId, array($siswaGroup->id, $guruGroup->id));
assert($resSiswaConflict['status'] === FALSE, "Kombinasi Siswa + staf harus ditolak.");
echo "[PASS] Penolakan kombinasi Siswa + staf berhasil: {$resSiswaConflict['msg']}\n";

// 5. Test validasi role kosong -> DITOLAK
$resEmpty = $ci->users->syncUserGroups($testUserId, array());
assert($resEmpty['status'] === FALSE, "Penyimpanan tanpa role harus ditolak.");
echo "[PASS] Penolakan role kosong berhasil: {$resEmpty['msg']}\n";

// 6. Test validasi ID role tidak valid
$resInvalid = $ci->users->syncUserGroups($testUserId, array(99999));
assert($resInvalid['status'] === FALSE, "ID role tidak valid harus ditolak.");
echo "[PASS] Penolakan ID role invalid berhasil.\n";

// 7. Kembalikan test user ke hanya Guru
$ci->users->syncUserGroups($testUserId, array($guruGroup->id));
$groupsFinal = $ci->ion_auth->get_users_groups($testUserId)->result();
assert(count($groupsFinal) === 1 && $groupsFinal[0]->name === 'guru', "User harus kembali memiliki role tunggal guru.");
echo "[PASS] Pengurangan role berhasil.\n";

echo "=== ALL MULTI-ROLE & TU TESTS PASSED ===\n";
