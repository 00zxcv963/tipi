# 第一节 函数的内部结构
在PHP中，函数有自己的作用域，同时在其内部可以实现各种语句的执行，最后返回最终结果值。在PHP的源码是可以发现，PHP内核将函数分为以下类型：

	[c]
	#define ZEND_INTERNAL_FUNCTION              1
	#define ZEND_USER_FUNCTION                  2  
	#define ZEND_OVERLOADED_FUNCTION            3
	#define ZEND_EVAL_CODE                      4
	#define ZEND_OVERLOADED_FUNCTION_TEMPORARY  5

其中的*ZEND_USER_FUNCTION*是用户函数，*ZEND_INTERNAL_FUNCTION*是内置的函数。
(PHP内部对不同种类的函数使用了不同的结构来进行实现?)

#### 1.用户函数（*ZEND_USER_FUNCTION*）:
用户自定义函数是非常常用的函数种类，如下面的代码，就定义了一个用户自定义的函数：

	[php]
	<?php 

	function tipi( $name ){
		$return = "Hi! " . $name;
		echo $return;
		return $return;
	}

	?>

这个示例中，对自定义函数传入了一个参数，并将其与*Hi!* 一起输出并做为返回值返回。从这个例子可以看出函数的基本特点：运行时声明、可以传参数、有值返回。当然，有些函数只是进行一些操作，并不一定有返回值，而事实上，在PHP的实现中，即使没有返回值，PHP内核也会“帮你“加上一个NULL来做为返回值。

通过 [<<第六节 变量的作用域>>][var-scope] 可知，ZE在执行过程中，会将运行时信息存储于_zend_execute_data中：
	
	[c]
	struct _zend_execute_data {
		//...省略部分代码
		zend_function_state function_state;
		zend_function *fbc; /* Function Being Called */
		//...省略部分代码
	};

在程序初始化的过程中，function_state也会进行初始化，function_state由两个部分组成：

	[c]
	typedef struct _zend_function_state {
		zend_function *function;
		void **arguments;
	} zend_function_state;

**arguments是一个指向函数参数的指针，而函数体本身则存储于*function中， *function是一个zend_function结构体，它最终存储了用户自定义函数的一切信息，它的具体结构是这样的：

	[c]
	typedef union _zend_function {
		zend_uchar type;    /* MUST be the first element of this struct! */

		struct {
			zend_uchar type;  /* never used */
			char *function_name; 	//函数名称
			zend_class_entry *scope; //函数所在的类作用域
			zend_uint fn_flags;		//函数类型，如用户自定义则为 #define ZEND_USER_FUNCTION 2  
			union _zend_function *prototype; //函数原型
			zend_uint num_args;		//参数数目
			zend_uint required_num_args; //需要的参数数目
			zend_arg_info *arg_info;  //参数信息指针
			zend_bool pass_rest_by_reference;
			unsigned char return_reference;  //返回值 
		} common;

		zend_op_array op_array;   //函数中的操作
		zend_internal_function internal_function;  
	} zend_function;

*zend_function*的结构中的op_array存储了该函数中所有的操作，当函数被调用时，ZE就会将这个op_array中的opline一条条顺次执行，并将最后的返回值返回。



#### 2.内部函数（*ZEND_INTERNAL_FUNCTION*）:


	[c]
	typedef struct _zend_internal_function {
		/* Common elements */
		zend_uchar type;
		char * function_name;
		zend_class_entry *scope;
		zend_uint fn_flags;
		union _zend_function *prototype;
		zend_uint num_args;
		zend_uint required_num_args;
		zend_arg_info *arg_info;
		zend_bool pass_rest_by_reference;
		unsigned char return_reference;
		/* END of common elements */

		void (*handler)(INTERNAL_FUNCTION_PARAMETERS);
		struct _zend_module_entry *module;
	} zend_internal_function;


[var-scope]:            ?p=chapt03/03-06-php-scope
