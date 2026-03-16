<section id="auth">
    <h3>Reset Password</h3>

    <form action="{index}" method="post" id="verifyRegister">

        <p>Code not found or has expired.</p>
        <input type="submit" name="submit" value="Home" id="yf-goHome" />

    </form>
</section>
<script>
    document.querySelector('#yf-goHome').addEventListener('click', function(e){
        e.preventDefault();
        window.location.href = '{baseHref}';
    })
</script>