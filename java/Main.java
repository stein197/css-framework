public class Main{
	public static void main(String ...args) throws Exception{
		BracketParser.BracePair pair = new BracketParser.BracePair(BracketParser.BRACE_CORNER);
		System.out.println(pair.start + pair.end);
	}
}