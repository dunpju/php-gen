/**
pom:
<!-- https://mvnrepository.com/artifact/commons-codec/commons-codec -->
<dependency>
    <groupId>commons-codec</groupId>
    <artifactId>commons-codec</artifactId>
    <version>1.15</version>
</dependency>
*/

// 代码:
import org.apache.commons.codec.digest.DigestUtils;

public class Sign {

    /**
     * 秘钥
     */
    public String secret = "priRip7DvDv4zy2Y1ODzhdlHUzg7y5qH";

    /**
     * @return
     */
    public static String getTimeStamp() {
        return String.valueOf(System.currentTimeMillis() / 1000);
    }

    /**
     * 签名
     *
     * @param data 所有请求参数加上appid、timestamp
     *             {
     *                 "param1": value1,
     *                 "param2": value2,
     *                 "appid": 3453127593,
     *                 "timestamp": 1653440794
     *             }
     * @return
     */
    public String sign(final Map<String, Object> data) {
        Set<String> keySet = data.keySet();
        String[] keyArray = keySet.toArray(new String[0]);
        Arrays.sort(keyArray);
        StringBuilder sb = new StringBuilder();
        for (int i = 0; i < keyArray.length; i++) {
            sb.append(keyArray[i]).append("=").append(data.get(keyArray[i]));
            if (i < keyArray.length - 1) {
                sb.append("&");
            }
        }
        return md5(secret + sb.toString() + secret);
    }

    /**
     * md5
     *
     * @param str
     * @return
     */
    public static String md5(String str) {
        return DigestUtils.md5Hex(str);
    }
}