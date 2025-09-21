<div style="width:100%;">
    <?php
    echo $this->Session->flash('auth');
    ?>
    <table style="margin-left:auto;margin-right:auto;">
        <tr>
            <td style="text-align:right;width:250px;padding-right:50px">
                <?php if (Configure::read('MISP.welcome_logo') && file_exists(APP . '/files/img/custom/' . Configure::read('MISP.welcome_logo'))): ?>
                    <img src="<?= $this->Image->base64(APP . 'files/img/custom/' . Configure::read('MISP.welcome_logo')) ?>"
                        alt="<?= __('Logo') ?>" onerror="this.style.display='none';">
                <?php endif; ?>
            </td>
            <td style="width:460px">
                <span style="font-size:18px;">
                    <?php
                    if (Configure::read('MISP.welcome_text_top')) {
                        echo h(Configure::read('MISP.welcome_text_top'));
                    }
                    ?>
                </span><br /><br />
                <div>
                    <?php if (Configure::read('MISP.main_logo') && file_exists(APP . '/files/img/custom/' . Configure::read('MISP.main_logo'))): ?>
                        <img src="<?= $this->Image->base64(APP . 'files/img/custom/' . Configure::read('MISP.main_logo')) ?>"
                            style=" display:block; margin-left: auto; margin-right: auto;">
                    <?php else: ?>
                        <img src="<?php echo $baseurl ?>/img/misp-logo-s-u.png"
                            style="display:block; margin-left: auto; margin-right: auto;">
                    <?php endif; ?>
                </div>
                <?php
                if (true == Configure::read('MISP.welcome_text_bottom')):
                    ?>
                    <div style="text-align:right;font-size:18px;">
                        <?php
                        echo h(Configure::read('MISP.welcome_text_bottom'));
                        ?>
                    </div>
                    <?php
                endif;
                if ($formLoginEnabled):
                    echo $this->Form->create('User');
                    ?>
                    <legend><?php echo __('Login'); ?></legend>
                    <?php
                    echo $this->Form->input('email', array('autocomplete' => 'off', 'autofocus'));
                    echo $this->Form->input('password', array('autocomplete' => 'off'));
                    if (!empty(Configure::read('LinOTPAuth')) && Configure::read('LinOTPAuth.enabled') !== FALSE) {
                        echo $this->Form->input('otp', array('autocomplete' => 'off', 'type' => 'password', 'label' => 'OTP'));
                        echo "<div class=\"clear\">";
                        echo sprintf(
                            '%s <a href="%s/selfservice" title="LinOTP Selfservice">LinOTP Selfservice</a> %s',
                            __('Visit'),
                            h(Configure::read('LinOTPAuth.baseUrl')),
                            __('for the One-Time-Password selfservice.')
                        );
                    }
                    ?>
                    <div class="clear">
                        <?php
                        echo empty(Configure::read('Security.allow_self_registration')) ? '' : sprintf(
                            '<a href="%s/users/register" title="%s">%s</a>',
                            $baseurl,
                            __('Registration will be sent to the administrators of the instance for consideration.'),
                            __('No account yet? Register now!')
                        );
                        ?>
                        <div class="clear">
                            <?php
                            echo empty(Configure::read('Security.allow_password_forgotten')) ? '' : sprintf(
                                '<a href="%s/users/forgot" title="%s">%s</a>',
                                $baseurl,
                                __('Initiate a password reset.'),
                                __('I have forgotten my password')
                            );
                            ?>
                        </div>
                        <?= $this->Form->button(__('Login'), array('class' => 'btn btn-primary')); ?>
                        <?php
                        echo $this->Form->end();
                endif;
                if (Configure::read('ApacheShibbAuth') == true) {
                    echo '<div class="clear" style="margin-top: 5px;"></div><a class="btn btn-info" href="/Shibboleth.sso/Login">Login with SAML</a>';
                }
                if (Configure::read('AadAuth') == true) {
                    echo '<div class="clear" style="margin-top: 5px;"></div><a class="btn btn-info" href="/users/login?AzureAD=enable">Login with AzureAD</a>';
                }
                if (Configure::read('OidcAuth') == true && Configure::read('OidcAuth.mixedAuth') == true) {
                    echo '<div class="clear" style="margin-top: 5px;"></div><a class="btn btn-info" href="/users/login?OidcAuth=enable">Login with OIDC</a>';
                }
                ?>
            </td>
            <td style="width:250px;padding-left:50px">
                <?php if (Configure::read('MISP.welcome_logo2') && file_exists(APP . '/files/img/custom/' . Configure::read('MISP.welcome_logo2'))): ?>
                    <img src="<?= $this->Image->base64(APP . 'files/img/custom/' . Configure::read('MISP.welcome_logo2')) ?>"
                        alt="<?= __('Logo2') ?>" onerror="this.style.display='none';">
                <?php endif; ?>
            </td>
        </tr>

        <!-- Display news and events  -->
        <tr style="margin-top: 10px">
            <td style="width:250px;padding-right:50px"></td>
            <td style="width:460px">
                <div class='row-layout' style="margin-bottom: 10px;">
                    <div id="news-button" class="btn btn-small btn-inverse chosen" style="margin-right: 5px">Latest News</div>
                    <div id="events-button" class="btn btn-small btn-inverse" style="">Latest Events</div>
                </div>
                <div id="news-list">
                    <?php if (!empty($newsArticles) && is_array($newsArticles) && count($newsArticles) > 0): ?>
                        <ul style="margin: 0 0 0 0;">
                        <?php foreach ($newsArticles as $news): ?>
                            <li class="news-item">
                                <a href="<?php echo $news['url']; ?>" target="_blank" style="color:inherit; text-decoration:none;"
                                    class="row-layout">
                                    <?php if (!empty($news['imageurl'])): ?>
                                        <img src="<?php echo $baseurl ?>/image-proxy.php?url=<?php echo urlencode($news['imageurl']); ?>"
                                            onerror="this.onerror=null; this.src='<?php echo $baseurl ?>/img/noImage.svg'"
                                            loading="lazy" class="news-headline-thumb" alt="">
                                    <?php else: ?>
                                        <img src="<?php echo $baseurl ?>/img/noImage.svg" loading="lazy" class="news-headline-thumb"
                                            alt="">
                                    <?php endif; ?>
                                    <div>
                                        <div style="font-weight:bold;">
                                            <?php echo h($news['title']); ?>
                                        </div>
                                        <div class="light-gray"><?php echo h($news['source']) ?></div>
                                        <div class="light-gray">
                                            <small><?php echo h(text: $news['datePublished']); ?></small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                <div id='events-list' style="display:none;">
                    <?php if (!empty($eventHeadlines) && is_array($eventHeadlines)) : ?> 
                        <ul style="margin: 0 0 0 0;">
                        <?php foreach ($eventHeadlines as $eventHeadline): ?>
                            <li class='event-item'>
                                <div style='font-weight: bold'><?php echo $eventHeadline['Event']['info'] ?></div>
                                <div>Threat Level: <?php switch ($eventHeadline['Event']['threat_level_id']) {
                                    case 1: echo 'High'; break;
                                    case 2: echo 'Medium'; break;
                                    case 3: echo 'Low'; break; 
                                    case 4: echo 'Undefined'; break;
                                } ?></div>
                                <div class='light-gray'>
                                    <small><?php echo h($eventHeadline['Event']['date']) ?></small>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <td style="width:250px;padding-left:50px"></td>
        </tr>
    </table>
</div>
<div class="clear" style="height: 50px;"></div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var btnEvents = document.getElementById("events-button");
        var btnNews = document.getElementById("news-button");
        var newsList = document.getElementById("news-list");
        var eventsList = document.getElementById("events-list");

        btnEvents.onclick = function () {
            newsList.style.display = "none";
            eventsList.style.display = "block";
            btnNews.classList.remove("chosen");
            btnEvents.classList.add("chosen");
            btnEventSetting.style.display = "block";
            btnNewsSetting.style.display = "none";
        }

        btnNews.onclick = function () {
            newsList.style.display = "block";
            eventsList.style.display = "none";
            btnNews.classList.add("chosen");
            btnEvents.classList.remove("chosen");            
            btnEventSetting.style.display = "none";
            btnNewsSetting.style.display = "block";
        }
    });
</script>

<script>
    $(function () {
        $('#UserLoginForm').submit(function (event) {
            event.preventDefault()
            submitLoginForm()
        });
    })

    function submitLoginForm() {
        var $form = $('#UserLoginForm')
        var url = $form.attr('action')
        var email = $form.find('#UserEmail').val()
        var password = $form.find('#UserPassword').val()
        var LinOTPAuth = <?= empty(Configure::read('LinOTPAuth')) ? 'false' : 'true' ?>;
        var LinOTPAuthEnabled = <?= empty(Configure::read('LinOTPAuth.enabled')) ? 'false' : 'true' ?>;

        if (LinOTPAuth && LinOTPAuthEnabled) {
            var otp = $form.find('#UserOtp').val()
        }
        if (!$form[0].checkValidity()) {
            $form[0].reportValidity()
        } else {
            fetchFormDataAjax(url, function (html) {
                var formHTML = $(html).find('form#UserLoginForm')
                if (!formHTML.length) {
                    window.location = baseurl + '/users/login'
                }
                $('body').append($('<div id="temp" style="display: none"/>').append(formHTML))
                var $tmpForm = $('#temp form#UserLoginForm')
                $tmpForm.find('#UserEmail').val(email)
                $tmpForm.find('#UserPassword').val(password)
                if (LinOTPAuth && LinOTPAuthEnabled) {
                    $tmpForm.find('#UserOtp').val(otp)
                }
                $tmpForm.submit()
            })
        }
    }
</script>