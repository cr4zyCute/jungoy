 <style>
    *{
    padding: 0;
    margin: 0;
    box-sizing: border-box;
}
footer{
    background-color: #111;
}
.footerContainer{
    width: 100%;
    padding: 70px 30px 20px ;
}
.socialIcons{
    display: flex;
    justify-content: center;
}
.socialIcons a{
    text-decoration: none;
    padding:  10px;
    background-color: white;
    margin: 10px;
    border-radius: 50%;
}
.socialIcons a i{
    font-size: 2em;
    color: black;
    opacity: 0,9;
}
/* Hover affect on social media icon */
.socialIcons a:hover{
    background-color: #111;
    transition: 0.5s;
}
.socialIcons a:hover i{
    color: white;
    transition: 0.5s;
}
.footerNav{
    margin: 30px 0;
}
.footerNav ul{
    display: flex;
    justify-content: center;
    list-style-type: none;
}
.footerNav ul li a{
    color:white;
    margin: 20px;
    text-decoration: none;
    font-size: 1.3em;
    opacity: 0.7;
    transition: 0.5s;

}
.footerNav ul li a:hover{
    opacity: 1;
}
.footerBottom{
    background-color: #000;
    padding: 20px;
    text-align: center;
}
.footerBottom p{
    color: white;
}
.designer{
    opacity: 0.7;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 400;
    margin: 0px 5px;
}
@media (max-width: 700px){
    .footerNav ul{
        flex-direction: column;
    } 
    .footerNav ul li{
        width:100%;
        text-align: center;
        margin: 10px;
    }
    .socialIcons a{
        padding: 8px;
        margin: 4px;
    }
}
 </style>
 <footer>
    <div class="footerContainer">
        <div class="socialIcons">
            <a href=""></i></a>
            <a href=""></i></a>
            <a href=""></i></a>
            <a href=""></i></a>
            <a href=""></i></a>
       
        </div>
        <div class="footerNav">
            <ul><li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="https://www.facebook.com/ctu.premier" target="_blank">Contact Us</a></li>
                <li><a href="https://www.ctu.edu.ph/" target="_blank" >Our University</a></li>
            </ul>
        </div>
        
    </div>
    <div class="footerBottom">
        <p>Copyright &copy;2023<span class="designer">CTU-BSIT</span></p>
    </div>
</footer>