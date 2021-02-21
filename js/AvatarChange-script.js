var rand1 = 0;  
var useRand = 0;  
images = new Array;  
images[1] = new Image();  
images[1].src = "img/avatar(1).png";  
images[2] = new Image();  
images[2].src = "img/avatar(2).png";  
images[3] = new Image();  
images[3].src = "img/avatar(3).png";  
images[4] = new Image();  
images[4].src = "img/avatar(4).png"; 
images[5] = new Image();  
images[5].src = "img/avatar(5).png";  
images[6] = new Image();  
images[6].src = "img/avatar(6).png"; 
images[7] = new Image();  
images[7].src = "img/avatar(7).png"; 
function change() {  
var imgnum = images.length - 1;  
do {  
var randnum = Math.random();  
rand1 = Math.round((imgnum - 1) * randnum) + 1;  
} while (rand1 == useRand);  
useRand = rand1;  
document.randimg.src = images[useRand].src;  
}  