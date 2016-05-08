/**
 * Themes Module Scripts for Phire CMS 2
 */

jax(document).ready(function(){
    if (jax('#themes-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#themes-form').checkAll(this.value);
            } else {
                jax('#themes-form').uncheckAll(this.value);
            }
        });
    }
});
