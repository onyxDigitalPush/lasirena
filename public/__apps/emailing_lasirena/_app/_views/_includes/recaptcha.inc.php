<?php
/**
 * View include
 */
/**
 * View include
 * @filename: recaptcha.inc.php
 * Location: _app/_views/_inludes
 * @Creator: R. Bernal (RBM) <info@novaigrup.com>
 * 	20150223 RBM Created
 */
?>
<div class="g-recaptcha" data-sitekey="<?php echo GOOGLE_RECAPTCHA_SITE_KEY; ?>" data-callback="recaptcha_callback"></div>
<noscript>
<div style="width: 302px; height: 352px;">
    <div style="width: 302px; height: 352px; position: relative;">
        <div style="width: 302px; height: 352px; position: absolute;">
            <iframe src="https://www.google.com/recaptcha/api/fallback?k=<?php echo GOOGLE_RECAPTCHA_SITE_KEY; ?>"
                    frameborder="0" scrolling="no"
                    style="width: 302px; height:352px; border-style: none;">
            </iframe>
        </div>
        <div style="width: 250px; height: 80px; position: absolute; border-style: none;
             bottom: 21px; left: 25px; margin: 0px; padding: 0px; right: 25px;">
            <textarea id="g-recaptcha-response" name="g-recaptcha-response"
                      class="g-recaptcha-response"
                      style="width: 250px; height: 80px; border: 1px solid #c1c1c1;
                      margin: 0px; padding: 0px; resize: none;" value="">
            </textarea>
        </div>
    </div>
</div>
</noscript>
<span id="recaptcha_alert" style="display:none; float:left; font-size:12px; color:#EA1F26;"></span>