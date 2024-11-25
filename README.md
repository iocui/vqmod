vQmod
=====

vQmod official repository

Please ensure you download from the RELEASES link above, or here - https://github.com/vqmod/vqmod/wiki/
The official source code splits out platforms and will not run correctly if you don't use a proper release zip from the above link

## 执行安装

```
composer require iocui/vqmod
```

控制台若有提问，键盘输入```y``` 回车确认安装，如显示 **VQMod install succee!** 即表示你现有的 ```composer``` 管理的项目。

> 在项目根 ```extend/vqmod/xml/``` 目录下创建xml规则文件, 即可无缝使用 vQmod 修改任意第三方依赖包源码。

## Examples

实战使用请参阅 [thinkphp项目](https://gitee.com/pgcao/tp6_vqmod)示例: https://gitee.com/pgcao/tp6_vqmod

> 如果你不想再继续使用 vQmod, 只需要将 ```extend/vqmod``` 目录直接删除或运行命令 ```composer remove iocui/vqmod``` 即可，但之前用 vQmod 处理的代码将无法再继续使用。

在性能上，经多个投产项目实际使用，并没有太多差别。对于要常改核心代码或想做插件开发的项目来说，vQmod 是一个很不错的选择，甚至你可以用 vQmod 的机制来做你的项目插件。 ^_^

## All Examples

更多vqmod相关示例：[vQmod Examples](https://github.com/vqmod/vqmod/wiki/Examples)