Transfomers
===========

What is it
----------

Transformer is a service class wich allow you to apply some transformation to the required raw media.


How to add a new customized transformers
----------------------------------------

Must implements MediaTransfomerInterface or extends a class whose implements it.

Declare the transformers in the service files and tag it as below :

```yml

    tms_media.transformer.default:
        class: Tms\Bundle\MediaBundle\Media\Transformer\DefaultMediaTransformer
        public: false
        tags:
            - { name: tms_media.transformer }
```

