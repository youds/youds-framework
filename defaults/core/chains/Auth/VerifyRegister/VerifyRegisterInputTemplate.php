
<section id="auth">
    <h3>Verify Email Address</h3>

    <form action="{users.register.verify}" method="post" id="verifyRegister">

        <span>Code:</span>
        <input type="text" required="" placeholder="Enter Code.." value="<?php echo $rd->getParameter('code') ?? ''; ?>" name="code" id="code" />
        <?php echo $it->recaptcha(); ?>
        <input type="submit" name="submit" value="Verify" />

    </form>
</section>