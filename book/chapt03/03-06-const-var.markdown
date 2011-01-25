# 第六节 PHP中的常量

常量与我们常见的变量有一些区别，它是在zval结构的基础上添加了一额外的内容。如下所示为PHP中常量的内部结构。
### 常量的内部结构

    [c]
    typedef struct _zend_constant {
        zval value; /* zval结构，PHP内部变量的存储结构，在第一小节有说明 */
        int flags;  /* 常量的标记如 CONST_PERSISTENT | CONST_CS */
        char *name; /* 常量名称 */
        uint name_len;  
        int module_number;  /* 模块号 */
    } zend_constant;

### define定义常量的过程
define是PHP的内置函数，在zend_builtin_functions.c文件中定义了此函数的实现。如下所示为部分源码：

    [c]

    /* {{{ proto bool define(string constant_name, mixed value, boolean case_insensitive=false)
       Define a new constant */
    ZEND_FUNCTION(define)
    {
            if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "sz|b", &name, &name_len, &val, &non_cs) == FAILURE) {
                    return;
            }

            ... // 类常量定义 此处不做介绍

            ... // 值类型判断和处理

            c.value = *val;
            zval_copy_ctor(&c.value);
            if (val_free) {
                    zval_ptr_dtor(&val_free);
            }
            c.flags = case_sensitive; /* non persistent */
            c.name = zend_strndup(name, name_len);
            c.name_len = name_len+1;
            c.module_number = PHP_USER_CONSTANT;
            if (zend_register_constant(&c TSRMLS_CC) == SUCCESS) {
                    RETURN_TRUE;
            } else {
                    RETURN_FALSE;
            }
    }
    /* }}} */

上面的代码是已经对对象和类常量做了简化处理，
其实现基本上是一个将传递的参数传递给新建的zend_constant结构，并将这个结构体注册到常量列表中的过程。

### defined判断常量是否设置
和define一样， defined的实现也在zend_builtin_functions.c文件，
其实现是一个读取参数变量，调用 zend_get_constant_ex函数获取常量的值来判断常量是否存在的过程。
而zend_get_constant_ex函数不仅包括了常规的常规的常量获取，还包括类常量的获取，
最后是通过zend_get_constant函数获取常量的值。在zend_get_constant函数中，基本上是通过下面的代码来获取常量的值。

    [c]
    zend_hash_find(EG(zend_constants), name, name_len+1, (void **) &c)

只是这个前面和后面对name有一些特别的处理。

### 标准常量的初始化
我们以cgi模式为例说明标准常量的初始化。
整个调用顺序如下所示：  
**[php_cgi_startup() -> php_module_startup() -> zend_startup() -> zend_register_standard_constants()]**

    [c]

    void zend_register_standard_constants(TSRMLS_D)
    {
        ... //  若干常量以REGISTER_MAIN_LONG_CONSTANT设置，
        REGISTER_MAIN_LONG_CONSTANT("E_ALL", E_ALL, CONST_PERSISTENT | CONST_CS);
        ...
    }

REGISTER_MAIN_LONG_CONSTANT宏展开是以zend_register_long_constant实现。
zend_register_long_constant函数将常量中值的类型，值，名称及模块号赋值给新的zend_constant。
并调用zend_register_constant添加到全局的常量列表中。

**[php_cgi_startup() -> php_module_startup() -> zend_startup() -> zend_register_standard_constants() -> zend_register_constant]**

    [c]
    ZEND_API void zend_register_long_constant(const char *name, uint name_len, long lval, int flags, int module_number TSRMLS_DC)
    {
        zend_constant c;

        c.value.type = IS_LONG;
        c.value.value.lval = lval;
        c.flags = flags;
        c.name = zend_strndup(name, name_len-1);
        c.name_len = name_len;
        c.module_number = module_number;
        zend_register_constant(&c TSRMLS_CC);
    }

zend_register_constant函数首先根据常量中的c->flags判断是否区分大小写，如果不区分，则名字统一为小写，如果包含"\\\\"，也统一成小写。否则为定义的名字
然后将调用下面的语句将当前常量添加到EG(zend_constants)。EG(zend_constants)是一个HashTable（这将在后面的章节中说明），下面的代码是将常量添加到这个HashTable中。

    [c]
    zend_hash_add(EG(zend_constants), name, c->name_len, (void *) c, sizeof(zend_constant), NULL)==FAILURE)


关于接口和类中的常量我们将在后面的类所在章节中详细说明。