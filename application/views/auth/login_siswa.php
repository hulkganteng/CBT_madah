<?php
$namaApp = isset($setting) && $setting->nama_aplikasi != null ? $setting->nama_aplikasi : 'Garuda CBT';
$logo = isset($setting) && $setting->logo_kanan != null ? base_url() . $setting->logo_kanan : base_url() . 'assets/img/favicon.png';
?>
<style>
    .login-siswa {
        display: flex; align-items: center; justify-content: center;
        min-height: 100vh; padding: 24px 16px;
        background: radial-gradient(circle at 50% 0%, #e7f4ee 0%, #eef2f4 45%, #e9edf1 100%);
    }
    .login-siswa-card {
        width: 100%; max-width: 400px; padding: 32px 28px 26px;
        border-radius: 14px; background: #fff;
        box-shadow: 0 18px 48px rgba(22, 61, 49, .14), 0 2px 6px rgba(22, 61, 49, .06);
    }
    .login-siswa-brand { text-align: center; margin-bottom: 22px; }
    .login-siswa-brand img { width: 64px; height: 64px; object-fit: contain; }
    .login-siswa-brand h1 { margin: 14px 0 4px; font-size: 1.18rem; font-weight: 600; color: #163d31; }
    .login-siswa-brand p { margin: 0; font-size: .78rem; color: #6b7772; }
    .login-siswa-badge {
        display: inline-block; margin-bottom: 4px; padding: 5px 12px; border-radius: 999px;
        background: #e6f5ee; color: #0a6b4c; font-size: .68rem; font-weight: 600;
        letter-spacing: .06em; text-transform: uppercase;
    }
    .login-siswa .form-group { margin-bottom: 16px; }
    .login-siswa label { margin-bottom: 6px; font-size: .78rem; font-weight: 500; color: #35423c; }
    .login-siswa .form-control {
        height: 48px; border-radius: 9px; font-size: .95rem;
        border: 1px solid #d5ddd8; background: #fbfdfb;
    }
    .login-siswa .form-control:focus { border-color: #0a6b4c; box-shadow: 0 0 0 3px rgba(10, 107, 76, .12); }
    .login-siswa .btn-masuk {
        width: 100%; height: 50px; margin-top: 6px; border: 0; border-radius: 9px;
        background: #0a6b4c; color: #fff; font-size: .95rem; font-weight: 600;
    }
    .login-siswa .btn-masuk:hover { background: #085a40; }
    .login-siswa .btn-masuk:disabled { opacity: .65; }
    .login-siswa-msg:empty { display: none; }
    .login-siswa-msg {
        margin-bottom: 14px; padding: 10px 12px; border-radius: 8px;
        background: #fdeaea; color: #8a1f1f; font-size: .78rem;
    }
    .login-siswa-foot { margin-top: 20px; text-align: center; font-size: .74rem; color: #7b8781; }
    .login-siswa-foot a { color: #0a6b4c; }
</style>

<main class="login-siswa">
    <section class="login-siswa-card">
        <div class="login-siswa-brand">
            <img src="<?= html_escape($logo) ?>" alt="Logo <?= html_escape($namaApp) ?>">
            <span class="login-siswa-badge">Peserta Ujian</span>
            <h1><?= html_escape($namaApp) ?></h1>
            <p>Masuk untuk mengerjakan ujian berbasis komputer</p>
        </div>

        <div id="infoMessage" class="login-siswa-msg" role="alert" aria-live="polite"><?= $message ?></div>

        <?= form_open('auth/cek_login', array('id' => 'login-siswa')) ?>
        <input type="hidden" name="portal" value="siswa">
        <div class="form-group">
            <label for="identity">Username peserta</label>
            <?= form_input($identity, '', 'required autocomplete="username" autocapitalize="none"') ?>
            <small class="form-text text-muted">Gunakan username yang diberikan oleh sekolah.</small>
        </div>
        <div class="form-group">
            <label for="password">Kata sandi</label>
            <?= form_input($password, '', 'required autocomplete="current-password"') ?>
            <small class="form-text text-muted">Pastikan kata sandi sudah benar sebelum masuk.</small>
        </div>
        <?= form_submit('submit', 'MASUK', array('id' => 'submit', 'class' => 'btn btn-masuk')) ?>
        <?= form_close() ?>

        <div class="login-siswa-foot">
            Token ujian diberikan oleh pengawas ruang.
        </div>
    </section>
</main>

<script type="text/javascript">
    $(function () {
        const base_url = '<?= base_url() ?>';
        const form = $('#login-siswa');
        const infobox = $('#infoMessage');

        form.on('submit', function (e) {
            e.preventDefault();
            const btn = $('#submit');
            infobox.text('');
            btn.prop('disabled', true).val('Memproses...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                dataType: 'json'
            }).done(function (data) {
                if (data && data.status) {
                    localStorage.setItem('garudaCBT.login', '1');
                    window.location.href = base_url + 'siswa/cbt';
                    return;
                }
                infobox.text(
                    (data && (data.failed || (data.invalid && (data.invalid.identity || data.invalid.password))))
                    || 'Login gagal. Periksa username dan kata sandi.'
                );
            }).fail(function (xhr) {
                let msg = 'Login gagal. Coba lagi.';
                try {
                    const res = JSON.parse(xhr.responseText);
                    msg = res.failed || res.message || msg;
                } catch (err) {}
                infobox.text(msg);
            }).always(function () {
                btn.prop('disabled', false).val('MASUK');
            });
        });
    });
</script>
