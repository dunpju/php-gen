var (
   appid = "3453127593"
   secret = "priRip7DvDv4zy2Y1ODzhdlHUzg7y5qH"
)

// timeStamp := time.Now().Unix()
// "timestamp": strconv.FormatInt(timeStamp, 10)
//
// data 所有请求参数加上appid、timestamp
//             {
//                 "param1": value1,
//                 "param2": value2,
//                 "appid": 3453127593,
//                 "timestamp": 1653440794
//             }
func GetSign(data map[string]string) string {
   data["appid"] = appid
   var keys []string
   for k := range data {
      keys = append(keys, k)
   }
   sort.Strings(keys)
   var dataParams []string
   for _, k := range keys {
      dataParams = append(dataParams, fmt.Sprintf("%s=%s", k, data[k]))
   }
   dataParamsStr := strings.Join(dataParams, "&")
   dataParamsStr = secret + dataParamsStr + secret
   m5 := md5.New()
   m5.Write([]byte(dataParamsStr))
   return strings.ToUpper(hex.EncodeToString(m5.Sum(nil)))
}