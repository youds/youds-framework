<section id="auth">

        <h3>Account</h3>
        <div>Email: <?php echo $user['email'];?> <?php if ($user['verified']):?> &#x2713;<small>verified</small><?php endif;?></div>

        <input type="submit" name="submit" value="Home" id="yf-goHome" />

</section>
<script>
    document.querySelector('#yf-goHome').addEventListener('click', function(){
        e.preventDefault();
        window.location.href = '{baseHref}';
    })
</script>
