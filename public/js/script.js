// ouvrir la modal afin d'ajouter un produit
function OpenAddModal(){
    let add_modal = document.querySelector('#add_modal');
    let closeByBg = document.querySelectorAll('.closeByBg')[0];

    add_modal.classList.add('active');
    closeByBg.style.display = 'block'
}

// fermer la modal ajouter un produit
function closeModal() {
    let add_modal = document.querySelector('#add_modal');
    let closeByBg = document.querySelectorAll('.closeByBg')[0];
    
    closeByBg.style.display = 'none'
    add_modal.classList.remove('active');
}

// ouvrir la modal panier
function OpenBasketModal() {
    let basket_modal = document.querySelector('#basket_modal');
    let closeByBg = document.querySelectorAll('.closeByBg')[0];

    basket_modal.classList.add('active');
    closeByBg.style.display = 'block';
}

// fermer la modal panier
function closeBasketBasket() {
    let basket_modal = document.querySelector('#basket_modal');
    let closeByBg = document.querySelectorAll('.closeByBg')[0];
    
    closeByBg.style.display = 'none'
    basket_modal.classList.remove('active');
}


//  Compteur de la qte pour ajouter 1 au panier
function addOne(){
    let count = document.querySelector('#count').value
    let span_qte = document.querySelector('#span_qte').innerHTML

    if(count < span_qte){
        document.querySelector('#count').value = count
        count = ++count
    }
}

//  Compteur de la qte pour retirer 1 au panier
function removeOne() {
    let count = document.querySelector('#count').value

    if(count > 1){
        count = --count
        document.querySelector('#count').value = count
    }
}