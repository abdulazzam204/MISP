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
                    <div id="news-settings-button" class="btn btn-small btn-inverse"
                            style="margin-left: auto;">
                            <i class="fas fa-cog"></i></div>
                    <div id="event-settings-button" class="btn btn-small btn-inverse"
                            style="margin-left: auto; display: none;">
                            <i class="fas fa-cog"></i></div>
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

<!-- Modal for News API Settings -->
<div id="news-settings-modal" class="modal background" style="display:none;">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>News Article Settings</h3>
        <br>
        <form id="news-settings-form">
            <label style="margin-bottom: 0px;">Topics:
                <input type="text" name="query" value="<?php echo $headlineSetting['query']; ?>"
                    style="margin-bottom: 0px;">
            </label>
            <div class="light-gray" style="margin-top: 0px; padding=0px"><small style="margin-top: 0px;">Keywords or
                    phrases of topics. Also allows the use of logical operators ( e.g : +cybersecurity -stocks "cyber
                    attacks" malware )</small></div>
            <br>
            <?php
                $currentLang = $headlineSetting['language'];
                $languages = ['ar' => 'Arabic','de' => 'German','en' => 'English','es' => 'Spanish','fr' => 'French','he' => 'Hebrew','it' => 'Italian','nl' => 'Dutch','no' => 'Norwegian','pt' => 'Portuguese','ru' => 'Russian','sv' => 'Swedish','ud' => 'Urdu','zh' => 'Chinese'];
            ?>
            <label style="margin-bottom: 0px;">Language:
                <select name="language">
                    <?php foreach ($languages as $code => $name): ?>
                        <option value="<?php echo h($code); ?>" <?php echo ($code === $currentLang) ? 'selected' : ''; ?>>
                            <?php echo h($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <br>
            <label>Sort By:
                <?php $currentSort = $headlineSetting['sortBy']; ?>
                <select name="sortBy">
                    <option value="publishedAt" <?php echo $currentSort === 'publishedAt' ? 'selected' : ''; ?>>Published At</option>
                    <option value="relevancy" <?php echo $currentSort === 'relevancy' ? 'selected' : ''; ?>>Relevancy
                    </option>
                    <option value="popularity" <?php echo $currentSort === 'popularity' ? 'selected' : ''; ?>>Popularity
                    </option>
                </select>
            </label><br>
            <label>Page Size:
                <input type="number" name="pageSize" value="10">
            </label><br>
            <label style="margin-bottom: 0px;">Domains:
                <input type="text" name="includeDomains" placeholder="example.com,another.com"
                    value='<?php echo $headlineSetting['includeDomains']; ?>' style="margin-bottom: 0px;">
            </label>
            <div class="light-gray" style="margin-top: 0px; padding=0px"><small style="margin-top: 0px;">A
                    comma-seperated string of news domains (eg bbc.co.uk, techcrunch.com, engadget.com) to restrict the
                    search to</small></div>
            <br>
            <label style="margin-bottom: 0px;">Exclude Domains:
                <input type="text" name="excludeDomains" placeholder="spam.com,irrelevant.com"
                    value='<?php echo $headlineSetting['excludeDomains']; ?>' style="margin-bottom: 0px;">
            </label>
            <div class="light-gray" style="margin-top: 0px; padding=0px"><small style="margin-top: 0px;">A
                    comma-seperated string of domains (eg bbc.co.uk, techcrunch.com, engadget.com) to remove from the
                    results.</small></div>
            <br>
            <button type="submit" class="btn btn-small btn-inverse">Save</button>
        </form>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var btnEvents = document.getElementById("events-button");
        var btnNews = document.getElementById("news-button");
        var newsList = document.getElementById("news-list");
        var eventsList = document.getElementById("events-list");

        var modal = document.getElementById("news-settings-modal");
        var btnNewsSetting = document.getElementById("news-settings-button");
        var btnEventSetting = document.getElementById("event-settings-button");
        var span = document.getElementsByClassName("close")[0];

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

        btnNewsSetting.onclick = function () {
            modal.style.display = "block";
        }

        span.onclick = function () {
            modal.style.display = "none";
        }

        window.onclick = function (event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        document.getElementById('news-settings-form').onsubmit = function (e) {
            e.preventDefault();
            var formData = new FormData(this);
            fetch('/news_headlines/saveSettings', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Settings saved!');
                    modal.style.display = "none";
                    location.reload(); // Optionally reload to apply new settings
                } else {
                    alert('Failed to save settings: ' + (data.error || 'Unknown error'));
                }
            }).catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving settings.');
            });
        };

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