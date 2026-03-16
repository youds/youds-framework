
<!-- Begin page -->
<section id="auth">
    <h3>Register</h3>

    <form action="{auth.register}" method="POST" enctype="multipart/form-data">
        <?php if (isset($errors)): foreach ($errors as $error):?><p class="error"><?php echo $error;?></p><?php endforeach; endif;?>
        <div class="input">
            <label for="yf-email">Email Address</label>
            <input type="text" required="" placeholder="Email Address.." name="email" id="yf-email"/>
        </div>

        <div class="input">
            <label for="yf-password">Password</label>
            <input type="password" required="" placeholder="Password.." name="password" id="yf-password">
        </div>

        <div class="input">
            <label for="confirmPassword">Confirm Password</label>

            <input type="password" required="" placeholder="Confirm Password.." name="confirmPassword" id="confirmPassword">
        </div>

        <div class="inputSubmit">
            <input type="submit" name="submit" value="Register" id="yf-submit" />
        </div>
    </form>
    <div class="input">
        <a href="{auth.login}">Already have an account?</a>
    </div>

</section>
<!-- end row -->

