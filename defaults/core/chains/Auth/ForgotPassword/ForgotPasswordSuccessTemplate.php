<section id="auth">
    <h3>Forgot Password</h3>
    <div>If the email address is in use an email has been sent to it.</div>

    <input type="submit" name="submit" value="Account" id="yf-goUsersAccount" />

</section>
<script>
    document.querySelector('#yf-goUsersAccount').addEventListener('click', function(){
        window.location.href = '{auth.account}';
    })
</script>
