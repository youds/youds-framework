


<!-- Begin page -->    

<section id="auth">
    <h3>Logged Out</h3>

    <div class="main">You have successfully logged out.</div>
    <input type="submit" name="submit" value="Home" id="yf-goHome" />

</section>
<script>
    document.querySelector('#yf-goHome').addEventListener('click', function(){
        e.preventDefault();
        window.location.href = '{baseHref}';
    })
</script>
