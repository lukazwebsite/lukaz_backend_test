import { defineStore } from 'pinia'

export const useAddCart = defineStore('carts', {
    state: () => {
        return {
            cart: [],
            totalItem: 0,
            totalPrice: 0,
            totalDiscount: 0,
            totalItemDiscount: 0,
        }
    },

    actions: {
        // Every mutation ends here so the four totals never drift apart.
        recalculate(){
            this.totalItem = this.cart.reduce((acc, item) => acc + Number(item.qty || 0), 0);
            this.totalPrice = this.cart.reduce((acc, item) => acc + (item.regular_price * item.qty), 0);
            this.totalDiscount = this.cart.reduce((acc, item) => acc + (item.current_price * item.qty), 0);
            this.totalItemDiscount = this.cart.reduce((acc, item) => acc + this.lineDiscount(item), 0);
        },

        // Flat per line discount, not per unit. Never more than the line itself.
        lineDiscount(item){
            let lineTotal = (item.current_price * item.qty);
            let entered = Number(item.item_discount || 0);

            if(isNaN(entered) || entered < 0){
                return 0;
            }

            return Math.min(entered, lineTotal);
        },

        cartStore(data){
            let checkExits = this.cart.filter(exitItem =>  exitItem.id === data.id);
            if(checkExits.length > 0){
                checkExits[0].qty +=1;
                checkExits[0].total = (checkExits[0].qty * checkExits[0].regular_price);


            }else{

                this.cart.push({ item_discount: 0, ...data });
            }

            this.recalculate();

        },

        porductIncrease(id){
            let checkExits = this.cart.filter(exitItem =>  exitItem.id === id);
            checkExits[0].qty +=1;
            checkExits[0].total = (checkExits[0].qty * checkExits[0].regular_price);

            this.recalculate();
        },

        porductDecrease(id){
            let checkExits = this.cart.filter(exitItem =>  exitItem.id === id);
            if(checkExits[0].qty > 1){
                checkExits[0].qty -=1;
                checkExits[0].total = (checkExits[0].qty * checkExits[0].regular_price);

                this.recalculate();

            }
        },

        productDiscount(id, value){
            let checkExits = this.cart.filter(exitItem =>  exitItem.id === id);

            if(!checkExits.length){
                return;
            }

            let entered = Number(value);

            checkExits[0].item_discount = (isNaN(entered) || entered < 0) ? 0 : entered;

            this.recalculate();
        },

        productRemove(index){
            this.cart.splice(index, 1);

            this.recalculate();

        }
    },

    getters: {
        addCartData: state => state.cart,
        cartItems: state => state.totalItem,
        cartPrice: state => state.totalPrice,
        discount: state => state.totalDiscount,
        itemDiscount: state => state.totalItemDiscount,
    },

    persist: true, // Enables persistence for this store

})
