export const useDataDate = () => {
    const dateMonthFunction = (data: any) => {
        let monthShort = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
        let d = new Date(data);
        let dYear = d.getFullYear();
        let dMonth = d.getMonth()
        let dDate = d.getDate()
        let dHours = d.getHours()
        let dMinutes = d.getMinutes()

        return dDate+'-'+monthShort[dMonth]+'-'+dYear+' '+dHours+':'+dMinutes;
    }


    const dateFunction = (data: any) => {

        let d = new Date(data);
        const dYear = d.getFullYear();
        const dMonth = String(d.getMonth() + 1).padStart(2, '0');
        const dDate = String(d.getDate()).padStart(2, '0');
        const dHours = d.getHours() > 0 ? String(d.getHours()).padStart(2, '0') : String('12').padStart(2, '0');
        const dMinutes = String(d.getMinutes()).padStart(2, '0');

        return dDate+'-'+dMonth+'-'+dYear+' '+dHours+':'+dMinutes;
    }


    const dayFunction = (data: any) => {

        let d = new Date(data);
        const dYear = d.getFullYear();
        const dMonth = String(d.getMonth() + 1).padStart(2, '0');
        const dDate = String(d.getDate()).padStart(2, '0');

        return dDate+'-'+dMonth+'-'+dYear;
    }

    return {
        dayFunction,
        dateFunction,
        dateMonthFunction
    }
}
