<schema xmlns="http://purl.oclc.org/dsdl/schematron">
	<title>A test schema for </title>
	<ns prefix="ae" uri="http://framework.youds.com/xml/config/global/envelope" />
	<ns prefix="ch" uri="http://framework.youds.com/xml/config/parts/config_handlers" />
	<pattern name="Base structure">
		<rule context="configuration">
			<assert test="ch:handlers">A configuration block contains handlers.</assert>
		</rule>
	</pattern>
</schema>
