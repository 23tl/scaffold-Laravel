# Services  
只负责**数据维护**的逻辑，数据怎么查询、更新、创建、删除，以及**相关联**的数据如何维护

## 岗位职责
1. 只负责**数据维护**的逻辑，数据怎么查询、更新、创建、删除，以及**相关联**的数据如何维护。所以 Services 中定义的方法名，应该是用来描述**数据是以怎样的形式去维护的**。比如 `searchUsersByPage`、`searchUsersById`和`insertUser`。
2. Services 只绑定**一个** model，**只允许**维护与当前 Repository 绑定的 Model 数据，**最多允许**维护与绑定的 Model 存在关联关系的 Model。比如，订单 OrderRepository 中会涉及到更新订单商品 OrderGoodsRepository 的数据。
3. Services 本质是在 Laravel ORM Model 中的一层封装，可以完全不用担心使用 Services 等同于放弃了 ORM 灵活性。原先常用的 ORM 方法**并没有移除**，只是位置从 Controller 中换到了 Services 而已。
4. Services 中不允许引入其他 Services