function afficherLieu() {
    let lieu = document.getById("lieu");
    // let rue = valueOf()
    // let ville = String(document.getElementById("lieu_ville").value,
    // let codePostal = String(document.getElementById("lieu_codePostal").value

    // let ville = document.getElementsByName("ville"); 
    // let codePostal = document.getElementsByName("code_postal"); 
    
    if (lieu) {
        window.consoleText(lieu.rue.toString());
        window.consoleText(lieu.ville.toString());
        window.consoleText(lieu.codePostal.toString());
    }
    return console.log(afficherLieu());
    
}
