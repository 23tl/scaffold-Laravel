# Logic
实现项目中的具体**功能**业务。所以 Service 中定义的方法名，应该是用来**描述功能或业务**的（动词+业务描述）。

## 岗位职责
1. 实现项目中的具体**功能**业务。所以 Logic 中定义的方法名，应该是用来**描述功能或业务**的（动词+业务描述）。比如`handleListPageDisplay`和`handleProfilePageDisplay`，分别对应用户列表展示和用户详情页展示的需求。
2. 处理 Controller 中传入的参数，进行**业务判断**
3. 调用 Service 处理**数据的逻辑处理**
4. Logic 可以不注入 Service，或者只注入与处理当前业务**存在数据关联**的 Service。比如，`EmailService`中或许就只有调用第三方 API 的逻辑，不需要更新维护系统中的数据，就不需要注入 Service；`OrderService`中实现了订单出库逻辑后，还需要生成相应的财务结算单据，就需要注入 `OrderService`和`FinancialDocumentRepository`，财务单据中的原单号关联着订单号，存在着数据关联。
5. Logic 中不允许调用其他 Logic，保持职责单一，如有需要，应该考虑 Controller 中调用