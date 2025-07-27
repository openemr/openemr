// This file contains intentional eslint violations for testing

var global_var = "bad variable";
var unused_variable = 123;

function badFunction(){
    var x=1;
    var y=2;
    if(x==y){
        console.log("Equal")
    }
    return x+y
}

function anotherBadFunction(param1,param2){
    if(param1==param2)
        return true
    else
        return false
}

class BadClass{
    constructor(){
        this.property=123;
    }
    
    badMethod(){
        var result=this.property*2;
        if(result>100){
            alert("Large number");
        }
        return result;
    }
}

var obj=new BadClass();
console.log(obj.badMethod())

// Missing semicolons, bad spacing, unused variables, etc.