define([], function () {

    class Presentation {

        constructor(moduleId, currentSlide, maxSlides) {
            this.moduleId = moduleId;
            this.currentSlide = currentSlide;
            this.maxSlides = maxSlides;
            this.module = $(".modtype_presentation[data-id='"+this.moduleId+"']")

            if(this.module.length == 0){
                this.module = $("#presentation-"+this.moduleId);
            }

            this.updatePagination();
            this.setSlide(this.currentSlide);
            this.appendEvents();

        }

        nextSlide(){

            if(this.currentSlide == this.maxSlides){
                return;
            }

            this.currentSlide++;

            $(this.module).find(`.slide.active`).removeClass('active');
            let slide = $(this.module).find(`.slide:nth-child(${this.currentSlide})`);
            let nextSlide = $(this.module).find(`.slide:nth-child(${this.currentSlide + 1})`);

            this.loadSlide(slide)
            this.loadSlide(nextSlide)
            this.updatePagination()
            $(slide).addClass('active');

        }

        prevSlide(){

            if(this.currentSlide == 1){
                return;
            }

            this.currentSlide--;

            $(this.module).find(`.slide.active`).removeClass('active');
            let slide = $(this.module).find(`.slide:nth-child(${this.currentSlide})`);
            let nextSlide = $(this.module).find(`.slide:nth-child(${this.currentSlide + 1})`);

            this.loadSlide(slide)
            this.loadSlide(nextSlide)
            this.updatePagination()
            $(slide).addClass('active');
        }

        setSlide(slide) {

            if (slide < 1 || slide > this.maxSlides) {
                return;
            }

            let element = $(this.module).find(`.slide:nth-child(${slide})`);
            let nextElement = $(this.module).find(`.slide:nth-child(${slide + 1})`);
            let prevElement = $(this.module).find(`.slide:nth-child(${slide - 1})`);

            $(this.module).find(`.slide.active`).removeClass('active');
            $(element).addClass('active');

            this.loadSlide(element);
            this.loadSlide(nextElement);
            this.loadSlide(prevElement);
        }

        loadSlide(element) {
            if (element.length == 0) {
                return;
            }
            if ($(element).find('img').attr('src') == '') {
                $(element).find('img').attr('src', $(element).data('url'));
            }
        }

        updatePagination(){
            $(this.module).find('.current-slide').html(this.currentSlide);
            $(this.module).find('.total-slides').html(this.maxSlides);
        }

        appendEvents(){

            const instance = this;

            $(this.module).find('.slide').on('click', function(e){
                var slideWidth = $(this).width();
                if (e.offsetX > slideWidth / 2) {
                    instance.nextSlide()
                } else if (e.offsetX < slideWidth / 2) {
                    instance.prevSlide()
                }
            });

            $(this.module).find('.fullscreen').on('click', function (e) {
                $(instance.module).find('.slides')[0].requestFullscreen();
            });

            $('html').on('keyup', function(e){
                if(e.keyCode == 39){
                    instance.nextSlide();
                }
                if(e.keyCode == 37){
                    instance.prevSlide();
                }
            });

            $(this.module).find('.prev-slide').on('click', function (e) {
                instance.prevSlide();
            });

            $(this.module).find('.next-slide').on('click', function (e) {
                instance.nextSlide();
            });
        }
    }

    return {
        init: function (moduleId, currentSlide, maxSlides) {
            new Presentation(moduleId, currentSlide, maxSlides);
        }
    }
});