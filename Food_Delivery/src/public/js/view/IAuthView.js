// SVOLTO DA: SALE MARIO
// MATRICOLA: 364432

export class IAuthView {
    constructor() {
        if (new.target === IAuthView) {
            throw new Error("IAuthView è una classe astratta e non può essere istanziata direttamente");
        }
    }
    
    bindSubmit(handler) {
        throw new Error("bindSubmit() è un metodo astratto e deve essere implementato");
    }
    
    showError(msg) {
        throw new Error("showError() è un metodo astratto e deve essere implementato");
    }
    
    render() {
        throw new Error("render() è un metodo astratto e deve essere implementato");
    }
    
    getHtml() {
        throw new Error("getHtml() è un metodo astratto e deve essere implementato");
    }
}