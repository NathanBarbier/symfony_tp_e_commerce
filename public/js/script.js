function addOne(){
    let count = document.querySelector('#count').value
    let span_qte = document.querySelector('#span_qte').innerHTML

    if(count < span_qte){
        count = ++count
        document.querySelector('#count').value = count
    }
}

function removeOne() {
    let count = document.querySelector('#count').value

    if(count > 1){
    count = --count
    document.querySelector('#count').value = count
    console.log(count);
    }
}
