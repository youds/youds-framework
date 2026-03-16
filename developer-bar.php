<style>
    @import url('https://fonts.googleapis.com/css2?family=Exo:ital,wght@0,100..900;1,100..900&family=IBM+Plex+Sans:ital,wght@0,100..700;1,100..700&display=swap');

    pre{padding:0;margin:0;}
    div#yf-developerBar {display:block;z-index:999999999;position:fixed;bottom:-43px;left:0px;right:0px;font-family: "IBM Plex Sans", "Courier New", sans-serif;width:100%;padding:0;margin:0;height:40px;background:#444444;border-top: 3px solid #222;transition: bottom 0.3s ease-in-out;cursor:pointer;text-align:center;}
    div#yf-developerBar div#yf-developerBar.yf-devBarOpen{text-align:left;}
    div#yf-developerBar div#yf-dumpContainer {position:fixed;bottom:10px;right:10px;background:yellow;padding:5px 24px;font-family: "Exo", "Courier New", sans-serif;border-radius:10px;line-height:30px;width:400px;text-align:left;}
    div#yf-developerBar div#yf-dumpContainer.yf-dumpClosed {display:none}
    div#yf-developerBar div#yf-dumpContainerToggle {width:43px;height:43px;line-height:43px;float:right;margin-left:30px;}
    div#yf-developerBar div#yf-dumpContainerToggle svg{margin-top:8px;}
    div#yf-developerBar div#yf-dumpContainer svg#yf-dumpClose{display:block;position:relative;top:5px;float:right;z-index:999;cursor:pointer;margin-right:-10px;}
    div#yf-developerBar div#yf-dumpContainer svg#yf-dumpClose.yf-closed{display:none}
    div#yf-developerBar div#yf-dumpContainer svg#yf-dumpCloseHover{display:none;position:relative;top:5px;float:right;z-index:999;cursor:pointer;margin-right:-10px;}
    div#yf-developerBar div#yf-dumpContainer .yf-dump{position:relative;padding:10px 11px;width:105%}
    div#yf-developerBar div#yf-dumpContainer .yf-dump svg.yf-dumpFileType {position:relative;top:7px;margin-left:-2px;margin-right:-3px;}
    div#yf-developerBar div#yf-dumpContainer .yf-dump svg.yf-dumpCopy {position:relative;top:-2px;left:-50px;cursor:pointer;}
    div#yf-developerBar div#yf-dumpContainer .yf-dump span svg.yf-dumpTick {display:none;}
    div#yf-developerBar div#yf-dumpContainer .yf-dump span.yf-dumpCopiedToClipboard svg {display:none;}
    div#yf-developerBar div#yf-dumpContainer .yf-dump span.yf-dumpCopiedToClipboard svg.yf-dumpTick {display:inline;position:relative;top:2px;}
    div#yf-developerBar div#yf-dumpContainer strong.yf-dumpFileName {position:absolute;overflow:hidden;display:inline-block;width:340px;height:36px;top:14px;left:40px;text-overflow:ellipsis;cursor:pointer;}
    div#yf-developerBar div#yf-dumpContainer strong.yf-dumpFileName:hover .yf-dumpMarquee {display: inline;}
    div#yf-developerBar div#yf-dumpContainer .yf-dumpMarquee {display: none;position:absolute;top:0px;white-space: nowrap;}
    div#yf-developerBar div#yf-dumpContainer .yf-dumpMarquee.yf-dumpCopiedToClipboard {color:green;}
    div#yf-developerBar div#yf-dumpContainer span.yf-dumpOutput{display:block;clear:both;font-size:11px;margin-top:7px;}
    div#yf-developerBar div#yf-dumpContainer span.yf-dumpCopyToClipboard {float:right;position:absolute;right:-4px;top:26px;}
    div#yf-developerBar div#yf-dumpContainer div.yf-dump svg {position:relative;top:8px;width:22px;height:22px;}
    a#yf-logoIcon {display:block;width:36px;height:36px;float:left;position:relative;margin-left:12px;top:1px;}
    a#yf-logoIcon img{width:36px;height:36px;}
    div#yf-dumpContainer strong.yf-dumpFileName:hover .yf-dumpFileNameInner {visibility: hidden;}
    div#yf-developerBar span.yf-icon a, div#yf-developerBar span.yf-icon a:visited, div#yf-developerBar span.yf-icon a:hover, div#yf-developerBar span.yf-icon a:active {text-decoration:none;display:inline-block;height:36px;width:115px;position:relative;text-align:left;color:whitesmoke;text-shadow:0 0 2px black}
    div#yf-developerBar div#yf-devBarIconControl{margin:auto;position:relative;}
    div#yf-developerBar div#yf-devBarIconControl div#yf-devBarIcons{margin:auto;position:relative;top:0px;}
    div#yf-developerBar div#yf-devBarIconControl div#yf-devBarIcons div#yf-devBarIconsContainer {height:36px;text-align:center;position:relative;}
    div#yf-developerBar div#yf-devBarIconControl div.yf-IconsControl{display:none;float:left;width:32px;height:32px;color:white;position:absolute;top:8px;}
    div#yf-developerBar span.yf-icon {display:inline-block;width:auto;padding-right:20px;text-align:left;color:whitesmoke;line-height:38px;font-size:12px;}
    div#yf-developerBar.yf-devBarOpen span.yf-icon {width:80%!important;margin-left:20px;}
    div#yf-developerBar.yf-devBarOpen span.yf-icon a,div#yf-developerBar.yf-devBarOpen span.yf-icon a:visited, div#yf-developerBar.yf-devBarOpen span.yf-icon a:hover, div#yf-developerBar.yf-devBarOpen span.yf-icon a:active {width:100%;text-align:center;}
    div#yf-developerBar span.yf-icon span.yf-closeContent{display:none;}
    div#yf-developerBar.yf-devBarOpen span.yf-icon span.yf-closeContent{display:inline-block;width:auto;position:relative;top:2px;left:6px;}
    div#yf-developerBar.yf-devBarOpen span.yf-icon span.yf-openText {display:inline-block;color:#aaa;padding-left:12px;}
    div#yf-developerBar.yf-devBarOpen span.yf-icon span.yf-openText strong{color:#bfbfbf;}
    div#yf-developerBar span.yf-icon span.yf-openText {display:none;}
    div#yf-developerBar span.yf-iconInner{display:inline-block;width:28px;height:28px;margin:5px 7px 9px 2px;text-align:left;background-color:azure;border-radius:7px;box-shadow:0 0 2px #000}
    div#yf-developerBar span.yf-iconInner svg{width:20px;height:20px;position:relative;top:4px;left:4px;}
    @media screen and (max-width: 1420px) {
        div#yf-developerBar span.yf-icon{width:120px;padding-right:15px;}
        div#yf-developerBar div#yf-devBarIconControl{width:864px;}
        div#yf-developerBar div#yf-devBarIconControl div#yf-devBarIcons{display:block;width:808px;overflow:hidden;}
        div#yf-developerBar div#yf-devBarIconControl div#yf-devBarIcons div#yf-devBarIconsContainer {width:max-content;text-align: left;white-space:nowrap;}
        div#yf-developerBar div#yf-devBarIconControl div.yf-IconsControl{display:block;float:left;width:32px;height:32px;color:white;position:absolute;top:8px;}
        div#yf-developerBar div#yf-devBarIconControl div#yf-devBarIconControlLeft{left:0px;}
        div#yf-developerBar div#yf-devBarIconControl div#yf-devBarIconControlRight{right:0px;}
    }
    @media screen and (max-width: 920px) {
        div#yf-developerBar span.yf-icon{width:115px;padding-right:15px;}
        div#yf-developerBar div#yf-devBarIconControl{width:464px;}
        div#yf-developerBar div#yf-devBarIconControl div#yf-devBarIcons{display:block;width:408px;overflow:hidden;}
        div#yf-developerBar div#yf-devBarIconControl div#yf-devBarIcons div#yf-devBarIconsContainer {width:max-content;text-align: left;white-space:nowrap;}
        div#yf-developerBar div#yf-devBarIconControl div.yf-IconsControl{display:block;float:left;width:32px;height:32px;color:white;position:absolute;top:8px;}
        div#yf-developerBar div#yf-devBarIconControl div#yf-devBarIconControlLeft{left:0px;}
        div#yf-developerBar div#yf-devBarIconControl div#yf-devBarIconControlRight{right:0px;}
    }

    <?php
     $files = [
        uniqid() => '/path/to/a-really-long-filename-just-for-you.php',
        uniqid() => '/a/very/long/path/that/needs/scrolling/from/depths/of/your/hard/disk/drives/file2.php',
        uniqid() => '/path/to/file3.php'

        ];
     foreach ($files as $uniqueId => $file): ?>
    div#yf-dumpContainer .yf-dumpMarquee.yf-<?php echo $uniqueId; ?> {
        animation: marquee <?php echo (int) (strlen($file) * 0.10); ?>s linear infinite alternate-reverse;
    }
    <?php endforeach; ?>

    @keyframes marquee {
        0% {transform: translateX(0%);}
        100% {transform: translateX(-100%);}
    }
</style>

<div id="yf-developerBar">
    <a href="https://framework.youds.com" id="yf-logoIcon"><img src="https://framework.youds.com/images/logoModernIconFlat.png" alt="Youds Framework Logo Icon"></a>
    <div id="yf-dumpContainerToggle">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="yellow" fill="none">
            <path d="M21.0127 14.4502L19.75 13.1875V9.5C19.75 5.21979 16.2802 1.75 12 1.75C7.71979 1.75 4.25 5.21979 4.25 9.5V13.1875L2.9873 14.4502C2.51512 14.9225 2.25 15.5635 2.25 16.2314C2.25017 17.6223 3.37767 18.7498 4.76855 18.75H19.2314C20.6223 18.7498 21.7498 17.6223 21.75 16.2314C21.75 15.5635 21.4849 14.9225 21.0127 14.4502Z" fill="currentColor"></path>
            <path d="M15.75 20C15.0345 21.3387 13.624 22.25 12 22.25C10.376 22.25 8.96548 21.3387 8.25 20H15.75Z" fill="currentColor"></path>
        </svg>
    </div>
    <div id="yf-devBarIconControl">
        <div id="yf-devBarIconControlLeft" class="yf-IconsControl">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="#f0ffff" fill="none">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M15.287 5.30711C15.5672 5.42319 15.75 5.69667 15.75 6.00002L15.75 18C15.75 18.3034 15.5673 18.5768 15.287 18.6929C15.0068 18.809 14.6842 18.7449 14.4697 18.5304L8.46967 12.5305C8.17678 12.2376 8.17677 11.7627 8.46966 11.4698L14.4696 5.4697C14.6841 5.2552 15.0067 5.19103 15.287 5.30711Z" fill="#f0ffff"></path>
            </svg>
        </div>
        <div id="yf-devBarIcons">
            <div id="yf-devBarIconsContainer">
                <span class="yf-icon" id="yf-chains">
                    <a href="#" class="yf-iconAnchor">
                        <span class="yf-iconInner">
                               <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="#000000" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M13.0226 4.42434C14.8474 2.52522 17.823 2.52522 19.6478 4.42433C21.4509 6.30095 21.4509 9.32764 19.6478 11.2043L16.4084 14.5757C16.126 14.8696 15.8142 15.1196 15.4814 15.3241L15.0242 14.5803L15.4814 15.3241C13.6795 16.4316 11.3201 16.1751 9.78322 14.5757C9.53777 14.3202 9.32551 14.043 9.14668 13.7501C8.85886 13.2788 9.00764 12.6633 9.479 12.3755C9.95035 12.0877 10.5658 12.2365 10.8536 12.7078C10.958 12.8788 11.0819 13.0406 11.2254 13.19L10.5043 13.8828L11.2254 13.19C12.1036 14.104 13.4251 14.2404 14.4341 13.6202C14.6221 13.5047 14.8012 13.3617 14.9662 13.19L18.2056 9.81857C19.265 8.71604 19.265 6.91256 18.2056 5.81003C17.1679 4.72999 15.5025 4.72999 14.4648 5.81003L13.7513 6.55263C13.3686 6.95087 12.7356 6.96352 12.3373 6.58087C11.9391 6.19822 11.9264 5.56518 12.3091 5.16693L13.0226 4.42434Z" fill="currentColor"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M7.59179 9.42433C9.41655 7.52522 12.3923 7.52522 14.217 9.42433C14.4624 9.67972 14.6746 9.95686 14.8534 10.2496C15.1413 10.721 14.9925 11.3364 14.5212 11.6243C14.0499 11.9121 13.4344 11.7634 13.1466 11.292C13.0422 11.1211 12.9183 10.9594 12.7749 10.81C11.7371 9.72999 10.0717 9.72999 9.03395 10.81L5.79453 14.1814C4.73516 15.284 4.73516 17.0874 5.79453 18.19C6.83229 19.27 8.49768 19.27 9.53544 18.19L10.2492 17.4471C10.6319 17.0489 11.2649 17.0362 11.6632 17.4189C12.0614 17.8015 12.074 18.4346 11.6914 18.8328L10.9776 19.5757C9.15284 21.4748 6.17713 21.4748 4.35237 19.5757C2.54921 17.699 2.54921 14.6724 4.35237 12.7957L7.59179 9.42433Z" fill="currentColor"></path></svg>
                        </span>
                        Chains <span class="yf-closeContent"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12" color="#f0ffff" fill="none"><path d="M12 1.25C17.9371 1.25 22.75 6.06294 22.75 12C22.75 17.9371 17.9371 22.75 12 22.75C6.06294 22.75 1.25 17.9371 1.25 12C1.25 6.06294 6.06294 1.25 12 1.25ZM9.63086 8.22461C9.2381 7.90427 8.65909 7.92689 8.29297 8.29297C7.92698 8.65909 7.90429 9.23813 8.22461 9.63086L8.29297 9.70703L10.5859 12L8.29395 14.293C7.90357 14.6835 7.90344 15.3166 8.29395 15.707C8.68447 16.0972 9.31758 16.0974 9.70801 15.707L12 13.4141L14.292 15.707L14.3682 15.7754C14.7608 16.0957 15.3399 16.0729 15.7061 15.707C16.0721 15.3411 16.0954 14.7619 15.7754 14.3691L15.7061 14.293L13.4131 12L15.707 9.70703L15.7754 9.63086C16.0957 9.23812 16.073 8.65909 15.707 8.29297C15.3409 7.92689 14.7619 7.90427 14.3691 8.22461L14.293 8.29297L12 10.5859L9.70703 8.29297L9.63086 8.22461Z" fill="#f0ffff"></path></svg></span>
                        <span class="yf-openText">
                            There are <strong><code>30</code></strong> chains over <strong><code>5 modules</code></strong> with <strong><code>5897</code></strong> lines of code
                        </span>
                    </a>
                </span>

                <span class="yf-icon" id="yf-database">
                    <a href="#" class="yf-iconAnchor">
                        <span class="yf-iconInner">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="#000000" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M21 5L21 10.7666L20.9905 10.7766C19.1522 12.7448 16.4961 13.9468 11.9947 13.9995C9.62244 13.999 7.86278 13.6634 6.43875 13.1223C5.11695 12.6201 4.04069 11.9245 3 11.0851L3 5C3 4.19711 3.43749 3.55194 3.96527 3.08401C4.49422 2.61504 5.20256 2.2384 5.99202 1.94235C7.57833 1.34749 9.70269 1 12 1C14.2973 1 16.4217 1.34749 18.008 1.94235C18.7974 2.2384 19.5058 2.61504 20.0347 3.08401C20.5625 3.55194 21 4.19711 21 5ZM5.29209 4.58052C5.01022 4.83042 5 4.97447 5 5C5 5.02553 5.01022 5.16958 5.29209 5.41948C5.57279 5.66834 6.03602 5.93815 6.69427 6.18499C8.0034 6.67591 9.87903 7 12 7C14.121 7 15.9966 6.67591 17.3057 6.18499C17.964 5.93815 18.4272 5.66834 18.7079 5.41948C18.9898 5.16958 19 5.02553 19 5C19 4.97447 18.9898 4.83042 18.7079 4.58052C18.4272 4.33166 17.964 4.06185 17.3057 3.81501C15.9966 3.32409 14.121 3 12 3C9.87903 3 8.0034 3.32409 6.69427 3.81501C6.03602 4.06185 5.57279 4.33166 5.29209 4.58052ZM7.21611 10.2819C6.81943 10.1627 6.40119 10.3876 6.28195 10.7843C6.1627 11.181 6.38761 11.5992 6.78428 11.7184L6.89337 11.7513L6.89339 11.7513C7.46213 11.9228 8.14746 12.1293 8.88764 12.2417C9.29716 12.3039 9.67954 12.0223 9.7417 11.6128C9.80387 11.2032 9.52228 10.8209 9.11276 10.7587C8.48163 10.6629 7.89002 10.4848 7.30515 10.3087L7.30513 10.3087L7.21611 10.2819Z" fill="currentColor"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M12.0119 15.9998C15.9533 15.9545 18.8246 15.0822 21 13.4492V19.0003C21 19.8032 20.5625 20.4484 20.0347 20.9163C19.5058 21.3853 18.7974 21.7619 18.008 22.058C16.4217 22.6528 14.2973 23.0003 12 23.0003C9.70269 23.0003 7.57833 22.6528 5.99202 22.058C5.20256 21.7619 4.49422 21.3853 3.96527 20.9163C3.43749 20.4484 3 19.8032 3 19.0003L3 13.5723C3.81375 14.1187 4.7042 14.6031 5.7284 14.9923C7.42256 15.6359 9.43204 15.9999 12.0005 15.9999L12.0119 15.9998ZM7.21611 17.2821C6.81943 17.1628 6.40119 17.3877 6.28195 17.7844C6.1627 18.1811 6.38761 18.5993 6.78428 18.7186L6.89337 18.7514C7.46211 18.9229 8.14745 19.1295 8.88764 19.2418C9.29716 19.304 9.67954 19.0224 9.7417 18.6129C9.80387 18.2034 9.52228 17.821 9.11276 17.7588C8.48162 17.663 7.89 17.4849 7.30513 17.3089L7.21611 17.2821Z" fill="currentColor"></path></svg>
                        </span>
                        Database
                    </a>
                </span>

                <span class="yf-icon" id="yf-generator">
                    <a href="#" class="yf-iconAnchor">
                        <span class="yf-iconInner">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="#000000" fill="none"><path d="M17.5828 14.2041C17.8216 13.5986 18.6784 13.5986 18.9172 14.2041L18.9586 14.309C19.5418 15.7877 20.7123 16.9582 22.191 17.5414L22.2959 17.5828C22.9014 17.8216 22.9014 18.6784 22.2959 18.9172L22.191 18.9586C20.7123 19.5418 19.5418 20.7123 18.9586 22.191L18.9172 22.2959C18.6784 22.9014 17.8216 22.9014 17.5828 22.2959L17.5414 22.191C16.9582 20.7123 15.7877 19.5418 14.309 18.9586L14.2041 18.9172C13.5986 18.6784 13.5986 17.8216 14.2041 17.5828L14.309 17.5414C15.7877 16.9582 16.9582 15.7877 17.5414 14.309L17.5828 14.2041Z" fill="currentColor"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M1.25 4C1.25 2.48122 2.48122 1.25 4 1.25H18C19.5188 1.25 20.75 2.48122 20.75 4V14.9175C20.4734 14.5602 20.245 14.1638 20.08 13.7455C19.4251 12.0848 17.0749 12.0848 16.42 13.7455C15.9638 14.902 14.902 15.9638 13.7455 16.42C12.0848 17.0749 12.0848 19.4251 13.7455 20.08C14.598 20.4163 15.3593 21.0159 15.8849 21.75H4C2.48122 21.75 1.25 20.5188 1.25 19V4ZM7 6.25C6.58579 6.25 6.25 6.58579 6.25 7C6.25 7.41421 6.58579 7.75 7 7.75H15C15.4142 7.75 15.75 7.41421 15.75 7C15.75 6.58579 15.4142 6.25 15 6.25H7ZM7 10.75C6.58579 10.75 6.25 11.0858 6.25 11.5C6.25 11.9142 6.58579 12.25 7 12.25H15C15.4142 12.25 15.75 11.9142 15.75 11.5C15.75 11.0858 15.4142 10.75 15 10.75H7ZM7 15.25C6.58579 15.25 6.25 15.5858 6.25 16C6.25 16.4142 6.58579 16.75 7 16.75H11C11.4142 16.75 11.75 16.4142 11.75 16C11.75 15.5858 11.4142 15.25 11 15.25H7Z" fill="currentColor"></path></svg>
                        </span>
                        Generator
                    </a>
                </span>
                <span class="yf-icon" id="yf-integrations">
                    <a href="#" class="yf-iconAnchor">
                        <span class="yf-iconInner">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="#000000" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M3.5 1C2.11929 1 1 2.11929 1 3.5V11.5C1 12.8807 2.11929 14 3.5 14H6.06463C6.47583 17.023 8.80516 19.4348 11.7865 19.9718C12.8198 21.7807 14.7675 23 17 23C20.3137 23 23 20.3137 23 17C23 14.7675 21.7807 12.8198 19.9718 11.7865C19.4348 8.80516 17.023 6.47583 14 6.06463V3.5C14 2.11929 12.8807 1 11.5 1H3.5ZM11 17C11 17.2263 11.0125 17.4497 11.0369 17.6695C9.25002 16.8942 8 15.1133 8 13.0418C8 10.2573 10.2573 8 13.0418 8C15.1133 8 16.8942 9.25002 17.6695 11.0369C17.4497 11.0125 17.2263 11 17 11C13.6863 11 11 13.6863 11 17Z" fill="currentColor"></path></svg>
                        </span>
                        Integrations
                    </a>
                </span>

                <span class="yf-icon" id="yf-routing">
                    <a href="#" class="yf-iconAnchor">
                        <span class="yf-iconInner">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="#000000" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M18 1.25C15.3696 1.25 13.25 3.40183 13.25 6.04035C13.25 7.50263 13.9236 8.57844 14.6959 9.4597C15.034 9.84551 15.4072 10.2116 15.7574 10.5552L15.8877 10.6832C16.2841 11.0731 16.6474 11.4392 16.958 11.8249C17.4807 12.4742 18.5059 12.4976 19.0494 11.8468C19.3701 11.4627 19.7392 11.0935 20.1381 10.6975L20.182 10.6539L20.1821 10.6539C20.5597 10.2791 20.9632 9.87858 21.327 9.45289C22.09 8.55999 22.75 7.47512 22.75 6.04035C22.75 3.40183 20.6304 1.25 18 1.25ZM18 4.25C17.0335 4.25 16.25 5.0335 16.25 6C16.25 6.9665 17.0335 7.75 18 7.75H18.009C18.9755 7.75 19.759 6.9665 19.759 6C19.759 5.0335 18.9755 4.25 18.009 4.25H18Z" fill="#141B34" /><path fill-rule="evenodd" clip-rule="evenodd" d="M1.25 19C1.25 16.9289 2.92893 15.25 5 15.25C7.07107 15.25 8.75 16.9289 8.75 19C8.75 21.0711 7.07107 22.75 5 22.75C2.92893 22.75 1.25 21.0711 1.25 19Z" fill="#141B34" /><path fill-rule="evenodd" clip-rule="evenodd" d="M5 10C5 7.65179 7.16489 6 9.5 6H11C11.5523 6 12 6.44772 12 7C12 7.55228 11.5523 8 11 8H9.5C7.96911 8 7 9.0345 7 10C7 10.9655 7.96911 12 9.5 12H12.5C14.8351 12 17 13.6518 17 16C17 18.3482 14.8351 20 12.5 20H11C10.4477 20 10 19.5523 10 19C10 18.4477 10.4477 18 11 18H12.5C14.0309 18 15 16.9655 15 16C15 15.0345 14.0309 14 12.5 14H9.5C7.16489 14 5 12.3482 5 10Z" fill="#141B34" /></svg>
                        </span>
                        Routing
                    </a>
                </span>
                <span class="yf-icon" id="yf-permissions">
                    <a href="#" class="yf-iconAnchor">
                        <span class="yf-iconInner">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="#000000" fill="none"><path d="M6.75 8C6.75 5.1005 9.1005 2.75 12 2.75C14.8995 2.75 17.25 5.1005 17.25 8C17.25 10.8995 14.8995 13.25 12 13.25C9.1005 13.25 6.75 10.8995 6.75 8Z" fill="currentColor"></path><path d="M4.25 20.5C4.25 16.2198 7.71979 12.75 12 12.75C16.2802 12.75 19.75 16.2198 19.75 20.5C19.75 20.9142 19.4142 21.25 19 21.25H5C4.58579 21.25 4.25 20.9142 4.25 20.5Z" fill="currentColor"></path></svg>
                        </span>
                        Permissions
                    </a>
                </span>
                <span class="yf-icon" id="yf-session">
                    <a href="#" class="yf-iconAnchor">
                        <span class="yf-iconInner">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="#000000" fill="none"><path d="M6.75 2C5.23122 2 4 3.23122 4 4.75V7.00012C4.00448 7.00004 4.00898 7 4.01348 7H20.5V4.75C20.5 3.23122 19.2688 2 17.75 2H6.75Z" fill="#141B34" /><path d="M20.5 8.5H4.01348C4.00898 8.5 4.00448 8.49996 4 8.49988V21.75C4 22.0135 4.13822 22.2576 4.36413 22.3931C4.59003 22.5287 4.87049 22.5357 5.10294 22.4118L12.25 18.6L19.3971 22.4118C19.6295 22.5357 19.91 22.5287 20.1359 22.3931C20.3618 22.2576 20.5 22.0135 20.5 21.75V8.5Z" fill="#141B34" /></svg>
                        </span>
                        Session
                    </a>
                </span>
                <span class="yf-icon" id="yf-testing">
                    <a href="#" class="yf-iconAnchor">
                        <span class="yf-iconInner">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="#000000" fill="none">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M8.05033 1.55292C7.66534 1.15694 7.03224 1.14802 6.63626 1.53301C6.24027 1.91799 6.23135 2.55109 6.61634 2.94708L7.88307 4.25H3.75C3.19772 4.25 2.75 4.69772 2.75 5.25C2.75 5.80229 3.19772 6.25 3.75 6.25H7.88307L6.61634 7.55292C6.23135 7.94891 6.24027 8.58201 6.63625 8.967C7.03224 9.35198 7.66534 9.34306 8.05033 8.94708L10.967 5.94708C11.3443 5.55896 11.3443 4.94104 10.967 4.55292L8.05033 1.55292ZM2.75 20V7.5H5.21144C4.92844 8.30226 5.11492 9.23131 5.76491 9.86324C6.65587 10.7295 8.08035 10.7094 8.94657 9.81843L11.8632 6.81843C12.7123 5.94516 12.7123 4.55485 11.8632 3.68158L9.49921 1.25H18.5C20.0188 1.25 21.25 2.48122 21.25 4V20C21.25 21.5188 20.0188 22.75 18.5 22.75H5.5C3.98122 22.75 2.75 21.5188 2.75 20ZM13.5 16.25C13.0858 16.25 12.75 16.5858 12.75 17C12.75 17.4142 13.0858 17.75 13.5 17.75H17.5C17.9142 17.75 18.25 17.4142 18.25 17C18.25 16.5858 17.9142 16.25 17.5 16.25H13.5ZM12.75 7C12.75 6.58579 13.0858 6.25 13.5 6.25H17.5C17.9142 6.25 18.25 6.58579 18.25 7C18.25 7.41421 17.9142 7.75 17.5 7.75H13.5C13.0858 7.75 12.75 7.41421 12.75 7ZM13.5 11.25C13.0858 11.25 12.75 11.5858 12.75 12C12.75 12.4142 13.0858 12.75 13.5 12.75H17.5C17.9142 12.75 18.25 12.4142 18.25 12C18.25 11.5858 17.9142 11.25 17.5 11.25H13.5ZM11.45 13.4C11.7814 13.6486 11.8485 14.1187 11.6 14.45L8.6 18.45C8.46955 18.624 8.27004 18.7327 8.05317 18.7482C7.8363 18.7636 7.62341 18.6841 7.46967 18.5304L5.96967 17.0304C5.67678 16.7375 5.67678 16.2626 5.96967 15.9697C6.26256 15.6768 6.73744 15.6768 7.03033 15.9697L7.91885 16.8582L10.4 13.55C10.6485 13.2187 11.1186 13.1515 11.45 13.4Z" fill="currentColor"></path></svg>
                        </span>
                        Testing
                    </a>
                </span>
                <span class="yf-icon" id="yf-translation">
                    <a href="#" class="yf-iconAnchor">
                        <span class="yf-iconInner">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="#000000" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M8 4.25C8.41421 4.25 8.75 4.58579 8.75 5V5.75H9.98318C9.99398 5.74976 10.0048 5.74976 10.0157 5.75H12C12.4142 5.75 12.75 6.08579 12.75 6.5C12.75 6.91421 12.4142 7.25 12 7.25H10.5649C10.27 8.16736 9.80405 9.14652 9.2017 10.0802C9.55243 10.4717 9.86543 10.8048 10.0303 10.9697C10.3232 11.2626 10.3232 11.7374 10.0303 12.0303C9.73744 12.3232 9.26256 12.3232 8.96967 12.0303C8.81869 11.8793 8.57572 11.6225 8.29748 11.317C7.74712 11.9825 7.12614 12.5929 6.45 13.1C6.11863 13.3485 5.64853 13.2814 5.4 12.95C5.15147 12.6186 5.21863 12.1485 5.55 11.9C6.19221 11.4183 6.78111 10.8215 7.29568 10.1704C7.14379 9.98789 6.99807 9.80756 6.8671 9.63779C6.65506 9.36292 6.44806 9.07318 6.32918 8.83541C6.14394 8.46493 6.29411 8.01442 6.66459 7.82918C7.03507 7.64394 7.48558 7.79411 7.67082 8.16459C7.7186 8.26015 7.84494 8.44958 8.05477 8.72159C8.09472 8.77337 8.13652 8.82665 8.17987 8.88113C8.50185 8.33733 8.77025 7.78393 8.97506 7.25H5C4.58579 7.25 4.25 6.91421 4.25 6.5C4.25 6.08579 4.58579 5.75 5 5.75H7.25V5C7.25 4.58579 7.58579 4.25 8 4.25Z" fill="currentColor"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M4 2.75C3.30964 2.75 2.75 3.30964 2.75 4V15C2.75 15.6904 3.30964 16.25 4 16.25H6.17157C6.50309 16.25 6.82104 16.1183 7.05546 15.8839L15.8839 7.05546C16.1183 6.82104 16.25 6.50309 16.25 6.17157V4C16.25 3.30964 15.6904 2.75 15 2.75L4 2.75ZM1.25 4C1.25 2.48122 2.48122 1.25 4 1.25L15 1.25C16.5188 1.25 17.75 2.48122 17.75 4V6.17157C17.75 6.90092 17.4603 7.60039 16.9445 8.11612L8.11612 16.9445C7.60039 17.4603 6.90092 17.75 6.17157 17.75H4C2.48122 17.75 1.25 16.5188 1.25 15V4Z" fill="currentColor"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M17.8284 6.25C17.0991 6.25 16.3996 6.53973 15.8839 7.05546L7.05546 15.8839C6.53973 16.3996 6.25 17.0991 6.25 17.8284V20C6.25 21.5188 7.48122 22.75 9 22.75H20C21.5188 22.75 22.75 21.5188 22.75 20V9C22.75 7.48122 21.5188 6.25 20 6.25H17.8284ZM15.0002 12.25C14.6774 12.25 14.3908 12.4566 14.2887 12.7628L12.2887 18.7628C12.1577 19.1558 12.3701 19.5805 12.763 19.7115C13.156 19.8425 13.5807 19.6301 13.7117 19.2372L14.2074 17.75H16.793L17.2887 19.2372C17.4197 19.6301 17.8444 19.8425 18.2374 19.7115C18.6303 19.5805 18.8427 19.1558 18.7117 18.7628L16.7117 12.7628C16.6096 12.4566 16.323 12.25 16.0002 12.25H15.0002ZM15.5002 13.8717L16.293 16.25H14.7074L15.5002 13.8717Z" fill="currentColor"></path></svg>
                        </span>
                        Translation
                    </a>
                </span>
                <span class="yf-icon" id="yf-validators">
                    <a href="#" class="yf-iconAnchor">
                        <span class="yf-iconInner">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="#000000" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M11.75 22.5C5.81294 22.5 1 17.6871 1 11.75C1 5.81294 5.81294 1 11.75 1C17.6871 1 22.5 5.81294 22.5 11.75C22.5 17.6871 17.6871 22.5 11.75 22.5ZM16.5182 9.39018C16.8718 8.9659 16.8145 8.33534 16.3902 7.98177C15.9659 7.62821 15.3353 7.68553 14.9818 8.10981L10.6828 13.2686L8.45711 11.0429C8.06658 10.6524 7.43342 10.6524 7.04289 11.0429C6.65237 11.4334 6.65237 12.0666 7.04289 12.4571L10.0429 15.4571C10.2416 15.6558 10.5146 15.7617 10.7953 15.749C11.076 15.7362 11.3384 15.606 11.5182 15.3902L16.5182 9.39018Z" fill="currentColor"></path></svg>
                        </span>
                        Validators
                    </a>
                </span>
            </div>

        </div>
        <div id="yf-devBarIconControlRight" class="yf-IconsControl">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="#f0ffff" fill="none">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M8.71304 5.30711C8.99329 5.19103 9.31588 5.25519 9.53038 5.46969L15.5303 11.4697C15.8232 11.7626 15.8232 12.2375 15.5303 12.5304L9.53033 18.5304C9.31583 18.7449 8.99324 18.809 8.71299 18.6929C8.43273 18.5768 8.25 18.3034 8.25 18L8.25005 6.00002C8.25005 5.69667 8.43278 5.4232 8.71304 5.30711Z" fill="#f0ffff"></path>
            </svg>
        </div>
    </div>
    <style>
        div.yf-devBarContent {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 155px;
            height: calc(100vh - 43px);
            width: 100%;
            background: azure;
        }
    </style>
    <div class="yf-devBarContent" id="yf-chainsContent">
        here
    </div>


    <script>
        var yfMouseMove = true;

        document.querySelectorAll('a.yf-iconAnchor').forEach(function (element) {
            element.addEventListener('click', function (e) {
                e.preventDefault();
                var contentId = this.parentElement.id + 'Content';
                var content = document.getElementById(contentId);
                if (content) {
                    content.style.display = content.style.display === 'none' ? 'block' : 'none';

                    if (content.style.display === 'block') {
                        yfMouseMove = false;

                        var yfClonedElement = this.parentElement.cloneNode(true);
                        yfClonedElement.querySelector('span.yf-closeContent').addEventListener('click', function () {
                            yfMouseMove = true;
                            document.getElementById('yf-devBarIconControl').style.display = 'block';
                            document.getElementById('yf-developerBar').classList.remove('yf-devBarOpen');
                        });
                        this.parentElement.parentElement.parentElement.parentElement.parentElement.insertBefore(yfClonedElement, null);
                        document.getElementById('yf-devBarIconControl').style.display = 'none';
                        document.getElementById('yf-developerBar').classList.add('yf-devBarOpen');



                    } else {
                        yfMouseMove = true;
                        document.getElementById('yf-devBarIconControl').style.display = 'block';
                        document.getElementById('yf-developerBar').classList.remove('yf-devBarOpen');

                    }
                }
            });
        });


    </script>
    <script>

        function yfCheckForEndOfChildren () {
            var yfCurrentElement = document.getElementById('yf-chains');
            var yfElementWidth = yfCurrentElement.offsetWidth;
            var yfContainerWidth = document.getElementById('yf-devBarIconsContainer').offsetWidth;
            var yfViewPort = document.getElementById('yf-devBarIcons').offsetWidth;
            var yfTotalWidth = yfContainerWidth - yfViewPort - yfElementWidth;
            var rightPosLeft, rightPosRight;
            rightPosLeft = rightPos + yfElementWidth;
            rightPosRight = rightPos - yfElementWidth;
            if (rightPosLeft < yfTotalWidth) {
                document.getElementById('yf-devBarIconControlRight').style.opacity = '0.5';
            }
            if (rightPosRight > 0) {
                document.getElementById('yf-devBarIconControlLeft').style.opacity = '0.5';
            }
        }

        var rightPos = 0;
        var yfCurrentElement = document.getElementById('yf-chains');
        function yfDevBarIconControl(dir) {
            if (document.getElementById('yf-devBarIconControlLeft').style !== 'none') {
                var el = document.getElementById('yf-devBarIconsContainer');
                if (el.style.right === '') {
                    el.style.right = '0px';
                }
                var yfElementWidth = yfCurrentElement.offsetWidth;
                var yfContainerWidth = document.getElementById('yf-devBarIconsContainer').offsetWidth;
                var yfViewPort = document.getElementById('yf-devBarIcons').offsetWidth;
                var yfTotalWidth = yfContainerWidth - yfViewPort - yfElementWidth;

                // Check if we're at the start
                if (dir === 'right' && rightPos <= (0 - yfTotalWidth)) {
                    document.getElementById('yf-devBarIconControlRight').style.opacity = '0.5';
                    return;
                }

                // Check if we're at the end
                if (dir === 'left' && yfTotalWidth <= 0) {
                    document.getElementById('yf-devBarIconControlLeft').style.opacity = '0.5';
                    return;
                }
                if ((dir === 'left' && rightPos < yfTotalWidth) || (dir === 'right' && rightPos > 0)) {

                    if (dir === 'left') {
                        rightPos += yfElementWidth;
                    } else {
                        rightPos -= yfElementWidth;
                    }

                    el.style.right = rightPos + 'px';
                    document.getElementById('yf-devBarIconControlLeft').style.opacity = '1';
                    document.getElementById('yf-devBarIconControlRight').style.opacity = '1';
                } else {
                    if (dir === 'left') {
                        document.getElementById('yf-devBarIconControlLeft').style.opacity = 0.5;
                    } else {
                        document.getElementById('yf-devBarIconControlRight').style.opacity = 0.5;
                    }
                }

                yfCheckForEndOfChildren();

            }
        }
        yfCheckForEndOfChildren();
        document.getElementById('yf-devBarIconControlRight').onclick = function () {yfDevBarIconControl('right');}
        document.getElementById('yf-devBarIconControlLeft').onclick = function ()  {yfDevBarIconControl('left');}


    </script>

    <div id="yf-dumpContainer" class="yf-dumpClosed">

        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" color="#000000" fill="none" id="yf-dumpClose">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 1.25C6.06294 1.25 1.25 6.06294 1.25 12C1.25 17.9371 6.06294 22.75 12 22.75C17.9371 22.75 22.75 17.9371 22.75 12C22.75 6.06294 17.9371 1.25 12 1.25ZM8.65852 9.74744C8.24288 9.38375 7.61112 9.42587 7.24744 9.84151C6.88375 10.2571 6.92587 10.8889 7.34151 11.2526L11.3415 14.7526C11.7185 15.0825 12.2815 15.0825 12.6585 14.7526L16.6585 11.2526C17.0742 10.8889 17.1163 10.2571 16.7526 9.84151C16.3889 9.42587 15.7571 9.38375 15.3415 9.74744L12 12.6712L8.65852 9.74744Z" fill="currentColor"></path>
        </svg>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" color="#000000" fill="none" id="yf-dumpCloseHover">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"></circle>
            <path d="M16 10.5C16 10.5 13.054 13.5 12 13.5C10.9459 13.5 8 10.5 8 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
        <?php foreach ($files as $uniqueId => $file): ?>
            <div class="yf-dump">
                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 24 24">
                    <defs><style>.st0 {fill: #1d1e1b;}.st1 {fill: #fff;}</style></defs>
                    <rect class="st0" x="1.65" y="1.65" width="20.71" height="20.71" rx="2" ry="2"/>
                    <g>
                        <path class="st1" d="M7.37,16.19c.1-.04.2-.04.3-.02.06.02.12.05.19.07s.15.04.24.04c.22,0,.42-.08.59-.25s.3-.35.38-.57l.23-.54-2.33-5.44c-.08-.18-.08-.32-.01-.43.07-.11.2-.16.4-.16h.65c.28,0,.47.14.56.41l1.15,3.12c.02.09.05.18.08.27s.05.18.08.27c.03.1.06.19.07.29h.02c.02-.1.04-.19.06-.28.02-.08.04-.17.07-.26s.05-.18.08-.27l1.08-3.13c.08-.28.27-.42.56-.42h.6c.18,0,.31.05.39.16.08.1.08.24.02.42l-2.52,6.46c-.1.28-.24.52-.4.72-.16.2-.34.37-.53.5s-.4.23-.62.29c-.22.06-.44.1-.67.1-.17,0-.32-.02-.47-.06s-.27-.08-.38-.12c-.14-.06-.23-.14-.27-.26s-.02-.25.04-.38l.11-.26c.06-.14.14-.22.24-.26h0Z"/>
                        <path class="st1" d="M14.09,10.19h-.3c-.33,0-.49-.17-.49-.5v-.2c0-.34.16-.5.49-.5h.3v-.29c0-.49.09-.88.28-1.17.18-.29.4-.52.66-.68s.51-.27.77-.32.47-.08.63-.08h.18c.33,0,.49.17.49.51v.31c0,.31-.17.47-.52.5-.1,0-.2.02-.31.04-.11.02-.22.06-.31.13s-.18.16-.25.29-.1.3-.1.52v.23h.82c.33,0,.49.17.49.5v.2c0,.34-.16.5-.49.5h-.82v4.32c0,.34-.17.5-.5.5h-.53c-.33,0-.49-.17-.49-.5v-4.32h0Z"/>
                    </g>
                </svg>
                <strong class="yf-dumpFileName">
                    <span class="yf-dumpFileNameInner">
                        <?php echo basename($file); ?>
                        <span class="yf-dumpCopyToClipboard" onclick="setClipboard(this, '<?php echo $file; ?>');">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="injected-svg yf-dumpTick" color="#000000" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M11.75 22.5C5.81294 22.5 1 17.6871 1 11.75C1 5.81294 5.81294 1 11.75 1C17.6871 1 22.5 5.81294 22.5 11.75C22.5 17.6871 17.6871 22.5 11.75 22.5ZM16.1901 9.64829C16.6861 9.40536 16.8912 8.80634 16.6483 8.31036C16.4053 7.81437 15.8063 7.60923 15.3103 7.85216C13.3545 8.81011 11.8402 10.4539 10.8372 11.7938C10.4896 12.2581 10.1962 12.6957 9.96026 13.071C9.68112 12.8292 9.40583 12.6289 9.16316 12.4689C8.88562 12.2859 8.63935 12.1481 8.45963 12.0548C8.36951 12.008 8.29538 11.9719 8.24153 11.9466C8.21458 11.934 8.19265 11.924 8.17625 11.9166L8.15586 11.9076L8.14888 11.9046L8.14622 11.9034L8.14412 11.9025C8.14388 11.9024 8.14412 11.9025 7.7502 12.8217L8.14412 11.9025C7.63649 11.685 7.04861 11.9201 6.83106 12.4277C6.61392 12.9344 6.84809 13.5211 7.3537 13.7397L7.35473 13.7401L7.35829 13.7417C7.3643 13.7444 7.37547 13.7495 7.3913 13.7569C7.423 13.7718 7.47309 13.7961 7.53765 13.8296C7.66731 13.897 7.85228 14.0002 8.06224 14.1387C8.49104 14.4214 8.97956 14.8217 9.33097 15.3237C9.53389 15.6136 9.8749 15.7746 10.2277 15.7472C10.5803 15.7198 10.8924 15.5078 11.0482 15.1902L11.0508 15.1851L11.0653 15.1563C11.079 15.1295 11.1005 15.0879 11.1297 15.0332C11.1882 14.9235 11.2772 14.7614 11.3954 14.56C11.6323 14.1564 11.9838 13.5994 12.4382 12.9924C13.3602 11.7609 14.6459 10.4046 16.1901 9.64829Z" fill="currentColor"></path></svg>

                        </span>
                    </span>
                    <span class="yf-dumpMarquee yf-<?php echo $uniqueId; ?>" onclick="setClipboard(this, '<?php echo $file; ?>', true);"><?php echo $file; ?></span>

                </strong>
                <span class="yf-dumpCopyToClipboard" onclick="setClipboard(this, '<?php echo $file; ?>');">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" class="injected-svg yf-dumpTick" color="#000000" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M11.75 22.5C5.81294 22.5 1 17.6871 1 11.75C1 5.81294 5.81294 1 11.75 1C17.6871 1 22.5 5.81294 22.5 11.75C22.5 17.6871 17.6871 22.5 11.75 22.5ZM16.1901 9.64829C16.6861 9.40536 16.8912 8.80634 16.6483 8.31036C16.4053 7.81437 15.8063 7.60923 15.3103 7.85216C13.3545 8.81011 11.8402 10.4539 10.8372 11.7938C10.4896 12.2581 10.1962 12.6957 9.96026 13.071C9.68112 12.8292 9.40583 12.6289 9.16316 12.4689C8.88562 12.2859 8.63935 12.1481 8.45963 12.0548C8.36951 12.008 8.29538 11.9719 8.24153 11.9466C8.21458 11.934 8.19265 11.924 8.17625 11.9166L8.15586 11.9076L8.14888 11.9046L8.14622 11.9034L8.14412 11.9025C8.14388 11.9024 8.14412 11.9025 7.7502 12.8217L8.14412 11.9025C7.63649 11.685 7.04861 11.9201 6.83106 12.4277C6.61392 12.9344 6.84809 13.5211 7.3537 13.7397L7.35473 13.7401L7.35829 13.7417C7.3643 13.7444 7.37547 13.7495 7.3913 13.7569C7.423 13.7718 7.47309 13.7961 7.53765 13.8296C7.66731 13.897 7.85228 14.0002 8.06224 14.1387C8.49104 14.4214 8.97956 14.8217 9.33097 15.3237C9.53389 15.6136 9.8749 15.7746 10.2277 15.7472C10.5803 15.7198 10.8924 15.5078 11.0482 15.1902L11.0508 15.1851L11.0653 15.1563C11.079 15.1295 11.1005 15.0879 11.1297 15.0332C11.1882 14.9235 11.2772 14.7614 11.3954 14.56C11.6323 14.1564 11.9838 13.5994 12.4382 12.9924C13.3602 11.7609 14.6459 10.4046 16.1901 9.64829Z" fill="currentColor"></path></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" class="injected-svg yf-dumpCopy" data-src="https://cdn.hugeicons.com/icons/copy-02-solid-standard.svg?v=2.0" xmlns:xlink="http://www.w3.org/1999/xlink" role="img" color="#000000"><path fill-rule="evenodd" clip-rule="evenodd" d="M7.99995 10.9C7.99995 9.29837 9.29833 8 10.9 8C11.4338 8 11.8666 8.43279 11.8666 8.96667C11.8666 9.50054 11.4338 9.93333 10.9 9.93333C10.3661 9.93333 9.93329 10.3661 9.93329 10.9C9.93329 11.4339 9.5005 11.8667 8.96662 11.8667C8.43275 11.8667 7.99995 11.4339 7.99995 10.9ZM12.8333 8.96667C12.8333 8.43279 13.2661 8 13.8 8H16.7C17.2338 8 17.6666 8.43279 17.6666 8.96667C17.6666 9.50054 17.2338 9.93333 16.7 9.93333H13.8C13.2661 9.93333 12.8333 9.50054 12.8333 8.96667ZM18.6333 8.96667C18.6333 8.43279 19.0661 8 19.6 8C21.2016 8 22.5 9.29837 22.5 10.9C22.5 11.4339 22.0672 11.8667 21.5333 11.8667C20.9994 11.8667 20.5666 11.4339 20.5666 10.9C20.5666 10.3661 20.1338 9.93333 19.6 9.93333C19.0661 9.93333 18.6333 9.50054 18.6333 8.96667ZM8.96662 12.8333C9.5005 12.8333 9.93329 13.2661 9.93329 13.8V16.7C9.93329 17.2339 9.5005 17.6667 8.96662 17.6667C8.43275 17.6667 7.99995 17.2339 7.99995 16.7V13.8C7.99995 13.2661 8.43275 12.8333 8.96662 12.8333ZM21.5333 12.8333C22.0672 12.8333 22.5 13.2661 22.5 13.8V16.7C22.5 17.2339 22.0672 17.6667 21.5333 17.6667C20.9994 17.6667 20.5666 17.2339 20.5666 16.7V13.8C20.5666 13.2661 20.9994 12.8333 21.5333 12.8333ZM8.96662 18.6333C9.5005 18.6333 9.93329 19.0661 9.93329 19.6C9.93329 20.1339 10.3661 20.5667 10.9 20.5667C11.4338 20.5667 11.8666 20.9995 11.8666 21.5333C11.8666 22.0672 11.4338 22.5 10.9 22.5C9.29833 22.5 7.99995 21.2016 7.99995 19.6C7.99995 19.0661 8.43275 18.6333 8.96662 18.6333ZM21.5333 18.6333C22.0672 18.6333 22.5 19.0661 22.5 19.6C22.5 21.2016 21.2016 22.5 19.6 22.5C19.0661 22.5 18.6333 22.0672 18.6333 21.5333C18.6333 20.9995 19.0661 20.5667 19.6 20.5667C20.1338 20.5667 20.5666 20.1339 20.5666 19.6C20.5666 19.0661 20.9994 18.6333 21.5333 18.6333ZM12.8333 21.5333C12.8333 20.9995 13.2661 20.5667 13.8 20.5667H16.7C17.2338 20.5667 17.6666 20.9995 17.6666 21.5333C17.6666 22.0672 17.2338 22.5 16.7 22.5H13.8C13.2661 22.5 12.8333 22.0672 12.8333 21.5333Z" fill="#000000"></path><path d="M3.74995 1C2.23117 1 0.999954 2.23122 0.999954 3.75V14.75C0.999954 16.2688 2.23117 17.5 3.74995 17.5H6.74995V10.9002C6.74995 8.6082 8.60797 6.75019 10.9 6.75019H17.5V3.75C17.5 2.23122 16.2687 1 14.75 1H3.74995Z" fill="#000000"></path></svg>
                        </span>
                <span class="yf-dumpOutput">(line <strong>54</strong>) string(15) "this was a test"</span>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<script>
    function yfMonitorDevBarStatus () {
        document.addEventListener('mousemove', function (e) {
            if (yfMouseMove) {
                var bar = document.getElementById('yf-developerBar');
                if (window.innerHeight - e.clientY < 120) {
                    bar.style.bottom = '0px';
                } else {
                    bar.style.bottom = '-43px';
                }
            }
        });
    }
    yfMonitorDevBarStatus();

    function setClipboard(el, value, full = false) {
        var tempInput = document.createElement("input");
        tempInput.style = "position: absolute; left: -1000px; top: -1000px";
        tempInput.value = value;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand("copy");
        document.body.removeChild(tempInput);
        if (!full) {
            el.classList.add('yf-dumpCopiedToClipboard');
            setTimeout(function () {
                el.classList.remove('yf-dumpCopiedToClipboard')
            }, 2500);
        } else {
            var container;
            container = el.parentNode.parentNode.querySelector('.yf-dumpCopyToClipboard');
            container.classList.add('yf-dumpCopiedToClipboard');
            setTimeout(function () {
                container.classList.remove('yf-dumpCopiedToClipboard')
            }, 2500);
        }
    }
    document.getElementById('yf-dumpClose').addEventListener('mouseover', function () {
        this.classList.add('yf-closed');
        document.getElementById('yf-dumpCloseHover').style.display = 'block';
    });
    document.getElementById('yf-dumpCloseHover').addEventListener('mouseout', function () {
        document.getElementById('yf-dumpClose').classList.remove('yf-closed');
        document.getElementById('yf-dumpCloseHover').style.display = 'none';
    });
    document.getElementById('yf-dumpCloseHover').addEventListener('click', function () {
        document.getElementById('yf-dumpContainer').classList.add('yf-dumpClosed');
    });

    const toggleContainer = document.getElementById('yf-dumpContainerToggle');
    const dumpContainer = document.getElementById('yf-dumpContainer');
    let isContainerPinned = false;

    function showContainer() {
        dumpContainer.classList.remove('yf-dumpClosed');
    }

    toggleContainer.addEventListener('click', function () {
        isContainerPinned = !isContainerPinned;
        if (isContainerPinned) {
            showContainer();
        }
    });


</script>
