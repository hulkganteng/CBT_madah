<?php $logo_app = $setting->logo_kanan == null ? base_url() . 'assets/img/favicon.png' : base_url() . $setting->logo_kanan; ?>
<main class="auth-shell">
    <section class="auth-brand-panel" aria-label="Identitas aplikasi">
        <div class="auth-brand">
            <img src="<?= $logo_app ?>" width="64" height="64" alt="Logo <?= html_escape($setting->nama_aplikasi) ?>">
            <div>
                <strong><?= $setting->nama_aplikasi ?></strong>
                <span><?= $setting->alamat ?></span>
            </div>
        </div>
        <div class="auth-brand-copy">
            <span class="auth-eyebrow">Sistem akademik terpadu</span>
            <h1>Kelola pembelajaran dan ujian dalam satu tempat.</h1>
            <p>Akses data sekolah, materi, penilaian, dan pelaksanaan CBT melalui akun Anda.</p>
        </div>
        <span class="auth-version">GarudaCBT · <?= APP_VERSION ?></span>
    </section>
    <section class="auth-form-panel">
        <div class="auth-card">
            <div class="auth-heading">
                <span class="auth-eyebrow">Selamat datang kembali</span>
                <h2>Masuk ke akun Anda</h2>
                <p>Gunakan akun yang telah terdaftar di sekolah.</p>
            </div>
                        <div id="infoMessage"><?php echo $message; ?></div>

                        <?= form_open("auth/cek_login", array('id' => 'login')); ?>
                        <input type="hidden" name="portal" value="staf">
                        <div class="form-group has-feedback">
                            <label for="identity">Nama pengguna</label>
                            <div class="auth-input-wrap">
                                <i class="fas fa-user" aria-hidden="true"></i>
                            <?= form_input($identity, '', 'required'); ?>
                            </div>
                            <div class="help-block"></div>
                        </div>
                        <div class="form-group has-feedback">
                            <label for="password">Kata sandi</label>
                            <div class="auth-input-wrap">
                                <i class="fas fa-lock" aria-hidden="true"></i>
                            <?= form_input($password, '', 'required'); ?>
                                <button id="toggle-password" class="auth-password-toggle" type="button" aria-label="Tampilkan kata sandi">
                                    <span class="fas fa-eye-slash" aria-hidden="true"></span>
                                </button>
                            </div>
                            <div class="help-block"></div>
                        </div>
                        <div class="auth-options">
                                <div class="icheck-cyan">
                                    <input type='checkbox' id="cbt-only" name='cbt-only'
                                           value='1' checked="checked"/>
                                    <label for="cbt-only">Masuk langsung ke CBT</label>
                                </div>
                        </div>
                        <?= form_submit('submit', lang('login_submit_btn'), array('id' => 'submit', 'class' => 'btn btn-primary btn-block auth-submit')); ?>
                        <?= form_close(); ?>
            <p class="text-center mt-3 mb-0" style="font-size:.76rem;color:#6b7772">
                Peserta ujian? <a href="<?= base_url('login-siswa') ?>">Masuk lewat halaman peserta</a>
            </p>
        </div>
    </section>
</main>

<script type="text/javascript">
    let base_url = '<?=base_url();?>';
    $(document).ready(function(){
        $('#myCarousel').carousel({
            interval: 1000 * 2,
            pause: 'none'
        });

        $('form#login input').on('change', function(){
            $(this).parent().removeClass('has-error');
            $(this).next().next().text('');
        });

        $('form#login').on('submit', function(e){
            e.preventDefault();
            e.stopImmediatePropagation();

            var infobox = $('#infoMessage');
            infobox.addClass('info-box align-items-center justify-content-center bg-gradient-info').text('Checking...');

            var btnsubmit = $('#submit');
            btnsubmit.attr('disabled', 'disabled').val('Wait...');

            const arrForm = $(this).serializeArray()
            const cbtOnly = arrForm.find(function (obj) {
                return obj.name === 'cbt-only'
            })
            localStorage.setItem('garudaCBT.login', cbtOnly !== undefined ? '1' : '0')

            $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(data){
                infobox.removeAttr('class').text('');
                btnsubmit.removeAttr('disabled').val('Login');
                console.log('login', data);
                if(data.status){
                    infobox.addClass('info-box align-items-center justify-content-center bg-gradient-success').text('Login Sukses');

                    const isLogin = localStorage.getItem('garudaCBT.login')
                    const isCbtMode = isLogin ? isLogin === '1' : false
                    let go = base_url + data.url;
                    if (isCbtMode && data.role === 'siswa') {
                        go = 'siswa/cbt'
                    }
                    window.location.href = go;
                }else{
                    if(data.invalid){
                        $.each(data.invalid, function(key, val){
                        $('[name="'+key+'"').parent().addClass('has-error');
                        $('[name="'+key+'"').next().next().text(val);
                        if(val == ''){
                            $('[name="'+key+'"').parent().removeClass('has-error');
                            $('[name="'+key+'"').next().next().text('');
                        }
                        });
                    }
                        if(data.failed){
                            infobox.addClass('info-box align-items-center justify-content-center bg-gradient-danger').text(data.failed);
                        }
                    }
                }
            });
        });

        $('#toggle-password').on('click', function () {
            // toggle the type attribute
            const type = $('#password').attr('type') === 'password' ? 'text' : 'password';
            $('#password').attr('type', type);
            // toggle the eye / eye slash icon
            $(this).find('span').toggleClass('fa-eye-slash fa-eye');
            $(this).attr('aria-label', type === 'password' ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
        });
    });
</script>
