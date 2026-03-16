<section id="auth">
    <h3>Reset Password</h3>

    <form action="{baseHref}" method="post" id="verifyRegister">

        <div>
            <div>
                <p>Your password was successfully changed.</p>
            </div>
        </div>

        <div>
            <div>
                You can now go <a href="{baseHref}">home</a>.
            </div>
        </div>
        <input type="submit" name="submit" value="Home" id="yf-goHome" />

</section>
<script>
    document.querySelector('#yf-goHome').addEventListener('click', function(){
        e.preventDefault();
        window.location.href = '{baseHref}';
    })
</script>
