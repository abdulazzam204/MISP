<div style="width:100%;">
    <?php
        echo $this->Session->flash('auth');

        function getUrlContent($url)
        {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_HTTPHEADER => [
                    'User-Agent: MISP-NewsWidget/1.0'  // 👈 Required by NewsAPI
                ]
            ]);
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Curl error: ' . curl_error($ch);
            }
            curl_close($ch);
            return $result;
        }

        // Get news headlines from NewsAPI
        $apiConfig = require APP . 'Config' . DS . 'config_api.conf.php';
        $apikey = $apiConfig['NewsApiKey'];
        // Search paramaters
        $query = urlencode('cybersecurity threats malware');
        $language = 'en';
        $sortBy = 'relevancy'; // relevancy / popularity / publishedAt
        $pageSize = 10;
        $includeDomains = ''; // comma separated
        $excludeDomains = ''; // comma separated
        // NewsAPI query
        $newsRequestUrl = 'https://newsapi.org/v2/everything?q='.$query.
            '&language='.$language.
            '&sortBy='.$sortBy.
            '&pageSize='.$pageSize.
            (!empty($includeDomains) ? '&domains='.$includeDomains : '').
            (!empty($excludeDomains) ? '&excludeDomains='.$excludeDomains : '').
            '&searchIn=title,description&apiKey='.$apikey;
        //$jsonResponse = file_get_contents($newsRequestUrl);
        $jsonResponse = getUrlContent($newsRequestUrl);
        if ($jsonResponse===false) {
            $articles = [];
            $error = error_get_last();
            echo "Error fetching API: " . $error['message'];
        } else {
            $data = json_decode($jsonResponse, true);
            $articles = [];
            if(!empty($data['articles'])) {
                foreach($data['articles'] as $article) {
                    $isoDate = $article['publishedAt'];
                    $date = new DateTime($isoDate, new DateTimeZone('Z'));
                    $date->setTimezone(new DateTimeZone('UTC'));
                    $formattedDate = $date->format('j F Y, H:i e');
                    $articles[] = [
                        'title' => $article['title'],
                        'url' => $article['url'],
                        'imageurl' => $article['urlToImage'],
                        'source' => $article['source']['name'],
                        'datePublished' => $formattedDate
                    ];
                }
            }
        }
    ?>
<table style="margin-left:auto;margin-right:auto;">
    <tr>
    <td style="text-align:right;width:250px;padding-right:50px">
        <?php if (Configure::read('MISP.welcome_logo') && file_exists(APP . '/files/img/custom/' . Configure::read('MISP.welcome_logo'))): ?>
            <img src="<?= $this->Image->base64(APP . 'files/img/custom/' . Configure::read('MISP.welcome_logo')) ?>" alt="<?= __('Logo') ?>" onerror="this.style.display='none';">
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
            <img src="<?= $this->Image->base64(APP . 'files/img/custom/' . Configure::read('MISP.main_logo')) ?>" style=" display:block; margin-left: auto; margin-right: auto;">
        <?php else: ?>
            <img src="<?php echo $baseurl?>/img/misp-logo-s-u.png" style="display:block; margin-left: auto; margin-right: auto;">
        <?php endif;?>
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
        <legend><?php echo __('Login');?></legend>
        <?php
            echo $this->Form->input('email', array('autocomplete' => 'off', 'autofocus'));
            echo $this->Form->input('password', array('autocomplete' => 'off'));
            if (!empty(Configure::read('LinOTPAuth')) && Configure::read('LinOTPAuth.enabled')!== FALSE) {
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
            <img src="<?= $this->Image->base64(APP . 'files/img/custom/' . Configure::read('MISP.welcome_logo2')) ?>" alt="<?= __('Logo2') ?>" onerror="this.style.display='none';">
        <?php endif; ?>
    </td>
    </tr>
    <tr style="margin-top: 10px">
        <td style="width:250px;padding-right:50px"></td>
        <td style="width:460px">
            <div class="row-layout" style="align-items: center;">
                <legend style="margin-right: 10px;"><?php echo __('Latest News');?></legend>
                <a id="create-button" class="btn btn-small btn-inverse" style="margin-left: auto; margin-bottom: 15px;" href="">
                    <i class="fas fa-cog"></i>
                </a>
            </div>
            <?php if (!empty($articles) && is_array($articles) && count($articles) > 0): ?>
                <?php foreach ($articles as $news): ?>
                    <li class="news-item">
                        <a href="<?php echo $news['url']; ?>" target="_blank" style="color:inherit; text-decoration:none;" class="row-layout">
                            <?php if(!empty($news['imageurl'])): ?>
                                <img src="<?php echo $baseurl?>/image-proxy.php?url=<?php echo urlencode($news['imageurl']); ?>" onerror="this.onerror=null; this.src='<?php echo $baseurl?>/img/noImage.svg'" loading="lazy" class="news-headline-thumb" alt="">
                            <?php else: ?>
                                <img src="<?php echo $baseurl?>/img/noImage.svg" loading="lazy" class="news-headline-thumb" alt="">
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
            <?php endif; ?>
        </td>
        <td style="width:250px;padding-left:50px"></td>
    </tr>
    </table>
</div>
<div class="clear" style="height: 50px;"></div>

<script>
$(function() {
    $('#UserLoginForm').submit(function(event) {
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
        fetchFormDataAjax(url, function(html) {
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
