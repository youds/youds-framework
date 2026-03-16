

<!-- Begin page -->
<section id="auth">

    <h3>Forgot Password</h3>


    <form action="{auth.forgotpassword}" method="post" id="verifyRegister">

        <div class="input">
            <label for="yf-email">Email Address</label>
            <input type="text" required="" placeholder="Email Address.." name="email" id="yf-email" />
        </div>
        <div class="inputSubmit">
            <input type="submit" name="submit" value="Reset Password" />
        </div>

    </form>
    <div class="input">
        <a href="{auth.register}">New user? Create an account</a>
    </div>

</section>
<!-- end row -->
        
